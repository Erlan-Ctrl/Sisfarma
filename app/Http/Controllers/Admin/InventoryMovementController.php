<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Store;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $store = $storeId > 0 ? $stores->firstWhere('id', $storeId) : null;

        $productId = (int) $request->integer('product_id', 0);
        $product = $productId > 0 ? Product::query()->find($productId) : null;

        $movements = InventoryMovement::query()
            ->with(['store', 'product', 'user'])
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->when($product, fn ($q) => $q->where('product_id', $product->getKey()))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.inventory.movements.index', [
            'stores' => $stores,
            'store' => $store,
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    public function create(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($stores->isEmpty()) {
            return redirect()
                ->route('admin.stores.create')
                ->with('status', 'Cadastre uma loja antes de movimentar o estoque.');
        }

        $storeId = (int) $request->integer('store_id', 0);
        $store = $storeId > 0
            ? ($stores->firstWhere('id', $storeId) ?? $stores->first())
            : $stores->first();

        $product = null;
        $productId = (int) $request->integer('product_id', 0);
        if ($productId > 0) {
            $product = Product::query()->find($productId);
        }

        if (! $product) {
            $code = trim((string) $request->get('code', ''));
            $digits = preg_replace('/\D+/', '', $code) ?? '';
            if ($digits !== '' && strlen($digits) >= 8) {
                $product = Product::query()->where('ean', $digits)->first();
            }
        }

        $q = trim((string) $request->get('q', ''));
        $matches = collect();
        if (! $product && $q !== '') {
            $digits = preg_replace('/\D+/', '', $q) ?? '';

            $matches = Product::query()
                ->where('is_active', true)
                ->where(function ($sub) use ($q, $digits) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('sku', 'ilike', "%{$q}%")
                        ->orWhere('ean', 'ilike', "%{$q}%");

                    if ($digits !== '' && strlen($digits) >= 8) {
                        $sub->orWhere('ean', $digits);
                    }
                })
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return view('admin.inventory.movements.create', [
            'stores' => $stores,
            'store' => $store,
            'product' => $product,
            'code' => (string) $request->get('code', ''),
            'q' => $q,
            'matches' => $matches,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService, AuditLogger $audit)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'string', 'in:in,out,adjust'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $storeId = (int) $validated['store_id'];
        $productId = (int) $validated['product_id'];
        $type = (string) $validated['type'];
        $quantity = (int) $validated['quantity'];

        $occurredAt = array_key_exists('occurred_at', $validated) && $validated['occurred_at']
            ? Carbon::parse($validated['occurred_at'])
            : now();
        $userId = optional($request->user())->getKey();

        $minQuantity = array_key_exists('min_quantity', $validated) && $validated['min_quantity'] !== null
            ? (int) $validated['min_quantity']
            : null;

        try {
            $movement = $inventoryService->apply(
                storeId: $storeId,
                productId: $productId,
                type: $type,
                quantity: $quantity,
                userId: $userId,
                occurredAt: $occurredAt,
                reason: $validated['reason'] ?? null,
                note: $validated['note'] ?? null,
                meta: null,
                minQuantity: $minQuantity,
            );
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['quantity' => $e->getMessage()])
                ->withInput();
        }

        $audit->log(
            action: 'inventory.movement.manual',
            auditable: $movement,
            before: null,
            after: [
                'id' => (int) $movement->getKey(),
                'store_id' => (int) $movement->store_id,
                'product_id' => (int) $movement->product_id,
                'type' => (string) $movement->type,
                'delta' => (int) $movement->delta,
                'quantity_before' => (int) $movement->quantity_before,
                'quantity_after' => (int) $movement->quantity_after,
                'occurred_at' => optional($movement->occurred_at)?->toISOString(),
                'reason' => $movement->reason,
            ],
            meta: [
                'min_quantity' => $minQuantity,
            ],
        );

        return redirect()
            ->route('admin.inventory.index', ['store_id' => $storeId])
            ->with('status', 'Movimentação registrada.');
    }
}
