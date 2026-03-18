<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $supplierId = (int) $request->integer('supplier_id', 0);
        $q = trim((string) $request->query('q', ''));

        $purchases = Purchase::query()
            ->with(['store', 'supplier', 'user'])
            ->when($storeId > 0, fn ($query) => $query->where('store_id', $storeId))
            ->when($supplierId > 0, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($q !== '', fn ($query) => $query->where('reference', 'ilike', "%{$q}%"))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'stores' => $stores,
            'suppliers' => $suppliers,
            'storeId' => $storeId,
            'supplierId' => $supplierId,
            'q' => $q,
        ]);
    }

    public function create(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $items = $request->old('items', []);
        if (! is_array($items)) {
            $items = [];
        }

        if ($items === []) {
            $prefillProductId = (int) $request->integer('product_id', 0);
            if ($prefillProductId > 0) {
                $items = [
                    [
                        'product_id' => $prefillProductId,
                        'quantity' => 1,
                        'unit_cost' => null,
                    ],
                ];
            }
        }

        $productIds = collect($items)
            ->filter(fn ($i) => is_array($i))
            ->map(fn ($i) => (int) ($i['product_id'] ?? 0))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $productsById = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return view('admin.purchases.create', [
            'stores' => $stores,
            'suppliers' => $suppliers,
            'items' => $items,
            'productsById' => $productsById,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService, AuditLogger $audit)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'reference' => ['nullable', 'string', 'max:80'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = $this->normalizeItems((array) ($validated['items'] ?? []), 'unit_cost');
        if ($items === []) {
            return back()
                ->withErrors(['items' => 'Informe ao menos 1 item.'])
                ->withInput();
        }

        $occurredAt = array_key_exists('occurred_at', $validated) && $validated['occurred_at']
            ? Carbon::parse($validated['occurred_at'])
            : now();

        $userId = optional($request->user())->getKey();

        $purchase = null;

        try {
            DB::transaction(function () use (&$purchase, $validated, $items, $occurredAt, $userId, $inventoryService): void {
                $purchase = Purchase::create([
                    'store_id' => (int) $validated['store_id'],
                    'supplier_id' => (int) $validated['supplier_id'],
                    'user_id' => $userId,
                    'reference' => $validated['reference'] ?? null,
                    'status' => 'posted',
                    'occurred_at' => $occurredAt,
                    'notes' => $validated['notes'] ?? null,
                    'items_count' => 0,
                    'total_cost' => 0,
                ]);

                $itemsCount = 0;
                $totalCost = 0.0;

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];
                    $unitCost = array_key_exists('unit_cost', $item) ? $item['unit_cost'] : null;
                    $unitCost = $unitCost !== null ? (float) $unitCost : null;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->getKey(),
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                    ]);

                    $itemsCount += $quantity;
                    if ($unitCost !== null) {
                        $totalCost += $unitCost * $quantity;
                    }

                    $inventoryService->apply(
                        storeId: (int) $validated['store_id'],
                        productId: $productId,
                        type: 'in',
                        quantity: $quantity,
                        userId: $userId,
                        occurredAt: $occurredAt,
                        reason: 'Compra #'.$purchase->getKey(),
                        note: $validated['reference'] ?? null,
                        meta: [
                            'source' => 'purchase',
                            'purchase_id' => (int) $purchase->getKey(),
                            'unit_cost' => $unitCost,
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
            return back()
                ->withErrors(['items' => $e->getMessage()])
                ->withInput();
        }

        $audit->log(
            action: 'purchase.posted',
            auditable: $purchase,
            before: null,
            after: [
                'id' => (int) $purchase->getKey(),
                'store_id' => (int) $purchase->store_id,
                'supplier_id' => (int) $purchase->supplier_id,
                'reference' => $purchase->reference,
                'occurred_at' => optional($purchase->occurred_at)?->toISOString(),
                'items_count' => (int) $purchase->items_count,
                'total_cost' => (string) $purchase->total_cost,
            ],
            meta: [
                'items' => collect($items)->map(fn ($i) => [
                    'product_id' => (int) $i['product_id'],
                    'quantity' => (int) $i['quantity'],
                    'unit_cost' => array_key_exists('unit_cost', $i) && $i['unit_cost'] !== null ? (float) $i['unit_cost'] : null,
                ])->values()->all(),
            ],
        );

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('status', 'Compra registrada.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['store', 'supplier', 'user', 'items.product']);

        return view('admin.purchases.show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return array<int, array{product_id:int, quantity:int, unit_cost: float|null}>
     */
    private function normalizeItems(array $raw, string $unitField): array
    {
        $grouped = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                continue;
            }

            $unit = $item[$unitField] ?? null;
            $unit = $unit !== null ? (float) $unit : null;

            if (! array_key_exists($productId, $grouped)) {
                $grouped[$productId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    $unitField => $unit,
                ];
                continue;
            }

            $grouped[$productId]['quantity'] += $quantity;
            if ($unit !== null) {
                $grouped[$productId][$unitField] = $unit;
            }
        }

        return array_values($grouped);
    }
}
