<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\Audit\AuditLogger;
use App\Services\Fiscal\NfeXmlParser;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NfeImportController extends Controller
{
    public function show()
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.purchases.import-xml', [
            'stores' => $stores,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request, NfeXmlParser $parser, InventoryService $inventoryService, AuditLogger $audit)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'xml' => ['required', 'file', 'max:5120', 'mimes:xml'],
            'create_products' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('xml');
        if (! $file) {
            return back()->withErrors(['xml' => 'Arquivo XML inválido.'])->withInput();
        }

        $xmlContent = (string) file_get_contents($file->getRealPath());
        if (trim($xmlContent) === '') {
            return back()->withErrors(['xml' => 'Arquivo XML vazio.'])->withInput();
        }

        $xmlHash = hash('sha256', $xmlContent);
        $existing = Purchase::query()->where('xml_hash', $xmlHash)->first();
        if ($existing) {
            return redirect()
                ->route('admin.purchases.show', $existing)
                ->with('status', 'Este XML já foi importado anteriormente.');
        }

        $data = $parser->parse($xmlContent);

        $supplierId = null;
        $supplierCnpj = $data['supplier']['cnpj'] ?? null;
        $supplierName = $data['supplier']['name'] ?? null;

        if ($supplierCnpj) {
            $supplier = Supplier::updateOrCreate(
                ['cnpj' => $supplierCnpj],
                [
                    'name' => $supplierName ?: 'Fornecedor '.$supplierCnpj,
                    'is_active' => true,
                ]
            );
            $supplierId = (int) $supplier->getKey();
        } elseif (! empty($validated['supplier_id'])) {
            $supplierId = (int) $validated['supplier_id'];
        } else {
            return back()
                ->withErrors(['supplier_id' => 'Não consegui identificar o fornecedor no XML. Selecione um fornecedor manualmente.'])
                ->withInput();
        }

        $storeId = (int) $validated['store_id'];
        $userId = optional($request->user())->getKey();

        $issuedAt = null;
        if (! empty($data['issued_at'])) {
            try {
                $issuedAt = Carbon::parse((string) $data['issued_at']);
            } catch (\Throwable) {
                $issuedAt = null;
            }
        }
        $occurredAt = $issuedAt ?: now();

        $nfeNumber = $data['number'] ?? null;
        $nfeSeries = $data['series'] ?? null;
        $nfeKey = $data['nfe_key'] ?? null;

        $reference = null;
        if ($nfeNumber) {
            $reference = 'NF-e '.$nfeNumber.($nfeSeries ? '/'.$nfeSeries : '');
        }
        if (! $reference && $nfeKey) {
            $reference = 'NF-e '.$nfeKey;
        }
        if ($reference !== null && strlen($reference) > 80) {
            $reference = substr($reference, 0, 80);
        }

        $createProducts = (bool) $request->boolean('create_products', true);

        $storagePath = 'fiscal/nfe/'.now()->format('Y-m').'/'.$xmlHash.'.xml';
        Storage::disk('local')->put($storagePath, $xmlContent);

        $purchase = null;

        try {
            DB::transaction(function () use (
                &$purchase,
                $storeId,
                $supplierId,
                $userId,
                $reference,
                $occurredAt,
                $data,
                $createProducts,
                $inventoryService,
                $storagePath,
                $xmlHash,
                $nfeKey,
                $nfeNumber,
                $nfeSeries,
            ): void {
                if ($nfeKey) {
                    $dup = Purchase::query()->where('nfe_key', $nfeKey)->exists();
                    if ($dup) {
                        throw new \RuntimeException('Já existe uma compra com esta chave de NF-e.');
                    }
                }

                $purchase = Purchase::create([
                    'store_id' => $storeId,
                    'supplier_id' => $supplierId,
                    'user_id' => $userId,
                    'reference' => $reference,
                    'nfe_key' => $nfeKey,
                    'nfe_number' => $nfeNumber,
                    'nfe_series' => $nfeSeries,
                    'xml_hash' => $xmlHash,
                    'xml_path' => $storagePath,
                    'status' => 'posted',
                    'occurred_at' => $occurredAt,
                    'notes' => 'Importado via XML de NF-e.',
                    'items_count' => 0,
                    'total_cost' => 0,
                ]);

                $itemsCount = 0;
                $totalCost = 0.0;

                foreach (($data['items'] ?? []) as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $ean = $item['ean'] ?? null;
                    $sku = $item['sku'] ?? null;
                    $name = (string) ($item['name'] ?? 'Produto');
                    $quantity = (int) ($item['quantity'] ?? 0);
                    $unitCost = array_key_exists('unit_cost', $item) && $item['unit_cost'] !== null
                        ? (float) $item['unit_cost']
                        : null;

                    if ($quantity < 1) {
                        continue;
                    }

                    $product = null;
                    if (is_string($ean) && $ean !== '') {
                        $product = Product::query()->where('ean', $ean)->first();
                    }
                    if (! $product && is_string($sku) && trim($sku) !== '') {
                        $product = Product::query()->where('sku', $sku)->first();
                    }

                    if (! $product) {
                        if (! $createProducts) {
                            throw new \RuntimeException('Produto não encontrado no catálogo: '.$name.' (EAN '.$ean.')');
                        }

                        $product = Product::create([
                            'name' => $name,
                            'sku' => is_string($sku) && trim($sku) !== '' ? trim($sku) : null,
                            'ean' => is_string($ean) && $ean !== '' ? $ean : null,
                            'supplier_id' => $supplierId,
                            'short_description' => null,
                            'description' => null,
                            'image_url' => null,
                            'price' => null,
                            'requires_prescription' => false,
                            'is_active' => true,
                            'is_featured' => false,
                        ]);
                    }

                    PurchaseItem::create([
                        'purchase_id' => $purchase->getKey(),
                        'product_id' => $product->getKey(),
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                    ]);

                    $itemsCount += $quantity;
                    if ($unitCost !== null) {
                        $totalCost += $unitCost * $quantity;
                    }

                    $inventoryService->apply(
                        storeId: $storeId,
                        productId: (int) $product->getKey(),
                        type: 'in',
                        quantity: $quantity,
                        userId: $userId,
                        occurredAt: $occurredAt,
                        reason: 'Compra #'.$purchase->getKey().' (XML)',
                        note: $reference,
                        meta: [
                            'source' => 'purchase',
                            'purchase_id' => (int) $purchase->getKey(),
                            'unit_cost' => $unitCost,
                            'imported_xml' => true,
                            'nfe_key' => $nfeKey,
                            'ncm' => $item['ncm'] ?? null,
                            'cest' => $item['cest'] ?? null,
                            'cfop' => $item['cfop'] ?? null,
                        ],
                        minQuantity: null,
                        lastUnitCost: $unitCost,
                        lastPurchaseAt: $occurredAt,
                        useTransaction: false,
                    );
                }

                $purchase->update([
                    'items_count' => $itemsCount,
                    'total_cost' => round($totalCost, 2),
                ]);
            });
        } catch (\Throwable $e) {
            // Do not keep the file if the import failed.
            Storage::disk('local')->delete($storagePath);

            return back()
                ->withErrors(['xml' => $e->getMessage()])
                ->withInput();
        }

        $audit->log(
            action: 'purchase.imported_xml',
            auditable: $purchase,
            before: null,
            after: [
                'id' => (int) $purchase->getKey(),
                'store_id' => (int) $purchase->store_id,
                'supplier_id' => (int) $purchase->supplier_id,
                'reference' => $purchase->reference,
                'nfe_key' => $purchase->nfe_key,
                'nfe_number' => $purchase->nfe_number,
                'nfe_series' => $purchase->nfe_series,
                'items_count' => (int) $purchase->items_count,
                'total_cost' => (string) $purchase->total_cost,
            ],
            meta: [
                'xml_hash' => $purchase->xml_hash,
                'xml_path' => $purchase->xml_path,
            ],
        );

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('status', 'Compra importada via XML.');
    }

    public function download(Purchase $purchase)
    {
        if (! $purchase->xml_path) {
            abort(404);
        }

        if (! str_starts_with((string) $purchase->xml_path, 'fiscal/nfe/')) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($purchase->xml_path)) {
            abort(404);
        }

        $name = 'NFe-'.(($purchase->nfe_number ?: $purchase->getKey())).'.xml';
        $name = preg_replace('/[^A-Za-z0-9_.-]+/', '_', (string) $name) ?: 'nfe.xml';

        return Storage::disk('local')->download($purchase->xml_path, $name);
    }
}
