<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function paymentMethods(): array
    {
        return [
            'dinheiro' => 'Dinheiro',
            'cartao' => 'Cartão',
            'pix' => 'Pix',
            'outro' => 'Outro',
        ];
    }

    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $q = trim((string) $request->query('q', ''));

        $sales = Sale::query()
            ->with(['store', 'user'])
            ->when($storeId > 0, fn ($query) => $query->where('store_id', $storeId))
            ->when($q !== '', fn ($query) => $query->where('reference', 'ilike', "%{$q}%"))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.sales.index', [
            'sales' => $sales,
            'stores' => $stores,
            'storeId' => $storeId,
            'q' => $q,
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function create(Request $request)
    {
        $stores = Store::query()
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
                        'unit_price' => null,
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

        return view('admin.sales.create', [
            'stores' => $stores,
            'items' => $items,
            'productsById' => $productsById,
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService, AuditLogger $audit)
    {
        $paymentMethods = array_keys($this->paymentMethods());

        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'reference' => ['nullable', 'string', 'max:80'],
            'payment_method' => ['nullable', 'string', 'max:20', 'in:'.implode(',', $paymentMethods)],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = $this->normalizeItems((array) ($validated['items'] ?? []), 'unit_price');
        if ($items === []) {
            return back()
                ->withErrors(['items' => 'Informe ao menos 1 item.'])
                ->withInput();
        }

        $occurredAt = array_key_exists('occurred_at', $validated) && $validated['occurred_at']
            ? Carbon::parse($validated['occurred_at'])
            : now();

        $userId = optional($request->user())->getKey();

        $sale = null;

        try {
            DB::transaction(function () use (&$sale, $validated, $items, $occurredAt, $userId, $inventoryService): void {
                $productIds = array_values(array_unique(array_map(fn ($i) => (int) $i['product_id'], $items)));
                $productsById = Product::query()
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                $sale = Sale::create([
                    'store_id' => (int) $validated['store_id'],
                    'user_id' => $userId,
                    'reference' => $validated['reference'] ?? null,
                    'status' => 'posted',
                    'payment_method' => $validated['payment_method'] ?? null,
                    'occurred_at' => $occurredAt,
                    'notes' => $validated['notes'] ?? null,
                    'items_count' => 0,
                    'total_amount' => 0,
                ]);

                $itemsCount = 0;
                $totalAmount = 0.0;

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    $unitPrice = array_key_exists('unit_price', $item) ? $item['unit_price'] : null;
                    if ($unitPrice === null) {
                        $unitPrice = optional($productsById->get($productId))->price;
                    }
                    $unitPrice = $unitPrice !== null ? (float) $unitPrice : null;

                    SaleItem::create([
                        'sale_id' => $sale->getKey(),
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);

                    $itemsCount += $quantity;
                    if ($unitPrice !== null) {
                        $totalAmount += $unitPrice * $quantity;
                    }

                    $inventoryService->apply(
                        storeId: (int) $validated['store_id'],
                        productId: $productId,
                        type: 'out',
                        quantity: $quantity,
                        userId: $userId,
                        occurredAt: $occurredAt,
                        reason: 'Venda #'.$sale->getKey(),
                        note: $validated['reference'] ?? null,
                        meta: [
                            'source' => 'sale',
                            'sale_id' => (int) $sale->getKey(),
                            'payment_method' => $validated['payment_method'] ?? null,
                            'unit_price' => $unitPrice,
                        ],
                        minQuantity: null,
                        lastUnitCost: null,
                        lastPurchaseAt: null,
                        useTransaction: false,
                    );
                }

                $sale->update([
                    'items_count' => $itemsCount,
                    'total_amount' => round($totalAmount, 2),
                ]);
            });
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['items' => $e->getMessage()])
                ->withInput();
        }

        $audit->log(
            action: 'sale.posted',
            auditable: $sale,
            before: null,
            after: [
                'id' => (int) $sale->getKey(),
                'store_id' => (int) $sale->store_id,
                'reference' => $sale->reference,
                'payment_method' => $sale->payment_method,
                'occurred_at' => optional($sale->occurred_at)?->toISOString(),
                'items_count' => (int) $sale->items_count,
                'total_amount' => (string) $sale->total_amount,
            ],
            meta: [
                'items' => collect($items)->map(fn ($i) => [
                    'product_id' => (int) $i['product_id'],
                    'quantity' => (int) $i['quantity'],
                    'unit_price' => array_key_exists('unit_price', $i) && $i['unit_price'] !== null ? (float) $i['unit_price'] : null,
                ])->values()->all(),
            ],
        );

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('status', 'Venda registrada.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['store', 'user', 'items.product']);

        return view('admin.sales.show', [
            'sale' => $sale,
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return array<int, array{product_id:int, quantity:int, unit_price: float|null}>
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
