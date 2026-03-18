<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $store = $storeId > 0
            ? ($stores->firstWhere('id', $storeId) ?? $stores->first())
            : $stores->first();

        $q = trim((string) $request->get('q', ''));
        $filter = (string) $request->get('filter', 'all');

        $inventories = Inventory::query()
            ->with(['product'])
            ->when($store, fn ($query) => $query->where('store_id', $store->getKey()))
            ->when($q !== '', function ($query) use ($q) {
                $digits = preg_replace('/\D+/', '', $q) ?? '';

                $query->whereHas('product', function ($sub) use ($q, $digits) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('sku', 'ilike', "%{$q}%")
                        ->orWhere('ean', 'ilike', "%{$q}%");

                    if ($digits !== '' && strlen($digits) >= 8) {
                        $sub->orWhere('ean', $digits);
                    }
                });
            })
            ->when($filter === 'below_min', function ($query) {
                $query->whereNotNull('min_quantity')
                    ->whereColumn('quantity', '<=', 'min_quantity');
            })
            ->when($filter === 'zero', function ($query) {
                $query->where('quantity', '<=', 0);
            })
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.inventory.index', [
            'stores' => $stores,
            'store' => $store,
            'inventories' => $inventories,
            'q' => $q,
            'filter' => $filter,
        ]);
    }

    public function replenishment(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $store = $storeId > 0 ? $stores->firstWhere('id', $storeId) : null;

        $mode = (string) $request->query('mode', 'below_min');
        if (! in_array($mode, ['below_min', 'excess'], true)) {
            $mode = 'below_min';
        }

        $query = Inventory::query()
            ->with(['store', 'product'])
            ->whereNotNull('min_quantity')
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()));

        if ($mode === 'below_min') {
            $query->whereColumn('quantity', '<=', 'min_quantity')
                ->orderByDesc(DB::raw('(min_quantity - quantity)'));
        } else {
            // Heuristic: >= 3x do minimo.
            $query->whereRaw('quantity >= (min_quantity * 3)')
                ->orderByDesc(DB::raw('(quantity - (min_quantity * 3))'));
        }

        $inventories = $query
            ->paginate(50)
            ->withQueryString();

        return view('admin.inventory.replenishment', [
            'stores' => $stores,
            'store' => $store,
            'mode' => $mode,
            'inventories' => $inventories,
        ]);
    }
}
