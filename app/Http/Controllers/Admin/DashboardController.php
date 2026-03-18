<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'counts' => [
                'users' => User::query()->count(),
                'categories' => Category::query()->count(),
                'products' => Product::query()->count(),
                'offers' => Offer::query()->count(),
                'stores' => Store::query()->count(),
                'suppliers' => Supplier::query()->count(),
                'purchases' => Purchase::query()->count(),
                'sales' => Sale::query()->count(),
                'inventory_items' => Inventory::query()->count(),
                'inventory_low' => Inventory::query()->whereNotNull('min_quantity')->whereColumn('quantity', '<=', 'min_quantity')->count(),
                'inventory_zero' => Inventory::query()->where('quantity', '<=', 0)->count(),
            ],
        ]);
    }
}
