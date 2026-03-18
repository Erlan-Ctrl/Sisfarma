<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $storeId = (int) $request->integer('store_id', 0);
        $store = $storeId > 0 ? $stores->firstWhere('id', $storeId) : null;

        $lowStock = Inventory::query()
            ->with(['store', 'product'])
            ->whereNotNull('min_quantity')
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->orderBy('quantity')
            ->limit(30)
            ->get();

        $zeroStock = Inventory::query()
            ->with(['store', 'product'])
            ->where('quantity', '<=', 0)
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        $missingEan = Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('ean')->orWhere('ean', '');
            })
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $recentMovements = InventoryMovement::query()
            ->with(['store', 'product', 'user'])
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $recentPurchases = Purchase::query()
            ->with(['store', 'supplier', 'user'])
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $recentSales = Sale::query()
            ->with(['store', 'user'])
            ->when($store, fn ($q) => $q->where('store_id', $store->getKey()))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'stores' => $stores,
            'store' => $store,
            'storeId' => $storeId,
            'lowStock' => $lowStock,
            'zeroStock' => $zeroStock,
            'missingEan' => $missingEan,
            'recentMovements' => $recentMovements,
            'recentPurchases' => $recentPurchases,
            'recentSales' => $recentSales,
        ]);
    }
}

