<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $fromStoreId = (int) $request->integer('from_store_id', 0);
        $toStoreId = (int) $request->integer('to_store_id', 0);
        $q = trim((string) $request->query('q', ''));

        $transfers = Transfer::query()
            ->with(['fromStore', 'toStore', 'user'])
            ->when($fromStoreId > 0, fn ($qr) => $qr->where('from_store_id', $fromStoreId))
            ->when($toStoreId > 0, fn ($qr) => $qr->where('to_store_id', $toStoreId))
            ->when($q !== '', fn ($qr) => $qr->where('reference', 'ilike', "%{$q}%"))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.transfers.index', [
            'stores' => $stores,
            'fromStoreId' => $fromStoreId,
            'toStoreId' => $toStoreId,
            'q' => $q,
            'transfers' => $transfers,
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

        return view('admin.transfers.create', [
            'stores' => $stores,
            'items' => $items,
            'productsById' => $productsById,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService, AuditLogger $audit)
    {
        $validated = $request->validate([
            'from_store_id' => ['required', 'integer', 'exists:stores,id'],
            'to_store_id' => ['required', 'integer', 'exists:stores,id', 'different:from_store_id'],
            'reference' => ['nullable', 'string', 'max:80'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = $this->normalizeItems((array) ($validated['items'] ?? []));
        if ($items === []) {
            return back()
                ->withErrors(['items' => 'Informe ao menos 1 item.'])
                ->withInput();
        }

        $occurredAt = array_key_exists('occurred_at', $validated) && $validated['occurred_at']
            ? Carbon::parse($validated['occurred_at'])
            : now();

        $userId = optional($request->user())->getKey();

        $transfer = null;

        try {
            DB::transaction(function () use (&$transfer, $validated, $items, $occurredAt, $userId, $inventoryService): void {
                $transfer = Transfer::create([
                    'from_store_id' => (int) $validated['from_store_id'],
                    'to_store_id' => (int) $validated['to_store_id'],
                    'user_id' => $userId,
                    'reference' => $validated['reference'] ?? null,
                    'status' => 'posted',
                    'occurred_at' => $occurredAt,
                    'notes' => $validated['notes'] ?? null,
                    'items_count' => 0,
                ]);

                $itemsCount = 0;

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    TransferItem::create([
                        'transfer_id' => $transfer->getKey(),
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);

                    $itemsCount += $quantity;

                    $inventoryService->apply(
                        storeId: (int) $validated['from_store_id'],
                        productId: $productId,
                        type: 'out',
                        quantity: $quantity,
                        userId: $userId,
                        occurredAt: $occurredAt,
                        reason: 'Transferência #'.$transfer->getKey().' (saída)',
                        note: $validated['reference'] ?? null,
                        meta: [
                            'source' => 'transfer',
                            'transfer_id' => (int) $transfer->getKey(),
                            'from_store_id' => (int) $validated['from_store_id'],
                            'to_store_id' => (int) $validated['to_store_id'],
                        ],
                        minQuantity: null,
                        lastUnitCost: null,
                        lastPurchaseAt: null,
                        useTransaction: false,
                    );

                    $inventoryService->apply(
                        storeId: (int) $validated['to_store_id'],
                        productId: $productId,
                        type: 'in',
                        quantity: $quantity,
                        userId: $userId,
                        occurredAt: $occurredAt,
                        reason: 'Transferência #'.$transfer->getKey().' (entrada)',
                        note: $validated['reference'] ?? null,
                        meta: [
                            'source' => 'transfer',
                            'transfer_id' => (int) $transfer->getKey(),
                            'from_store_id' => (int) $validated['from_store_id'],
                            'to_store_id' => (int) $validated['to_store_id'],
                        ],
                        minQuantity: null,
                        lastUnitCost: null,
                        lastPurchaseAt: null,
                        useTransaction: false,
                    );
                }

                $transfer->update([
                    'items_count' => $itemsCount,
                ]);
            });
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['items' => $e->getMessage()])
                ->withInput();
        }

        $audit->log(
            action: 'transfer.posted',
            auditable: $transfer,
            before: null,
            after: [
                'id' => (int) $transfer->getKey(),
                'from_store_id' => (int) $transfer->from_store_id,
                'to_store_id' => (int) $transfer->to_store_id,
                'reference' => $transfer->reference,
                'occurred_at' => optional($transfer->occurred_at)?->toISOString(),
                'items_count' => (int) $transfer->items_count,
            ],
            meta: [
                'items' => collect($items)->map(fn ($i) => [
                    'product_id' => (int) $i['product_id'],
                    'quantity' => (int) $i['quantity'],
                ])->values()->all(),
            ],
        );

        return redirect()
            ->route('admin.transfers.show', $transfer)
            ->with('status', 'Transferência registrada.');
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['fromStore', 'toStore', 'user', 'items.product']);

        return view('admin.transfers.show', [
            'transfer' => $transfer,
        ]);
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return array<int, array{product_id:int, quantity:int}>
     */
    private function normalizeItems(array $raw): array
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

            if (! array_key_exists($productId, $grouped)) {
                $grouped[$productId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ];
                continue;
            }

            $grouped[$productId]['quantity'] += $quantity;
        }

        return array_values($grouped);
    }
}
