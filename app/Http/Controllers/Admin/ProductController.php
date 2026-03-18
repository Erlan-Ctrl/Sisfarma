<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $supplierId = (int) $request->integer('supplier_id', 0);
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();

        $products = Product::query()
            ->with(['categories', 'supplier'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub
                        ->where('name', 'ilike', "%{$q}%")
                        ->orWhere('slug', 'ilike', "%{$q}%")
                        ->orWhere('sku', 'ilike', "%{$q}%")
                        ->orWhere('ean', 'ilike', "%{$q}%");
                });
            })
            ->when($supplierId > 0, fn ($query) => $query->where('supplier_id', $supplierId))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'q' => $q,
            'suppliers' => $suppliers,
            'supplierId' => $supplierId,
        ]);
    }

    public function apiSearch(Request $request)
    {
        $storeId = (int) $request->integer('store_id', 0);

        $id = (int) $request->integer('id', 0);
        $codeRaw = trim((string) $request->query('code', ''));
        $q = trim((string) $request->query('q', ''));
        $limit = (int) $request->integer('limit', 10);
        $limit = max(1, min(20, $limit));

        // Avoid pathological inputs causing slow queries / log spam.
        if (strlen($codeRaw) > 80) {
            $codeRaw = substr($codeRaw, 0, 80);
        }
        if (strlen($q) > 80) {
            $q = substr($q, 0, 80);
        }

        if ($id > 0) {
            $product = Product::query()
                ->whereKey($id)
                ->select(['id', 'name', 'ean', 'sku', 'price'])
                ->first();

            if (! $product) {
                return response()->json([
                    'ok' => false,
                    'product' => null,
                ]);
            }

            $productId = (int) $product->getKey();

            $inventoryRow = null;
            if ($storeId > 0) {
                $inventoryRow = DB::table('inventories')
                    ->where('store_id', $storeId)
                    ->where('product_id', $productId)
                    ->select(['quantity', 'last_unit_cost', 'last_purchase_at'])
                    ->first();
            }

            $stockTotal = (int) DB::table('inventories')
                ->join('stores', 'stores.id', '=', 'inventories.store_id')
                ->where('inventories.product_id', $productId)
                ->where('stores.is_active', true)
                ->sum('inventories.quantity');

            $otherStores = $this->stockOtherStores($productId, $storeId);

            $fallback = null;
            if ($storeId > 0 && ($inventoryRow?->last_unit_cost === null)) {
                $fallback = $this->lastCostFromPurchases($productId, $storeId);
            }

            return response()->json([
                'ok' => true,
                'product' => $this->presentApiProduct(
                    product: $product,
                    storeId: $storeId,
                    inventoryRow: $inventoryRow,
                    stockTotal: $stockTotal,
                    otherStores: $otherStores,
                    fallbackCost: $fallback,
                ),
            ]);
        }

        if ($codeRaw !== '') {
            $code = preg_replace('/\\s+/', '', $codeRaw) ?? '';
            $digits = preg_replace('/\\D+/', '', $code) ?? '';

            $product = Product::query()
                ->where(function ($query) use ($code, $digits) {
                    if ($digits !== '') {
                        $query->where('ean', $digits);
                    }
                    $query->orWhere('sku', $code);
                })
                ->select(['id', 'name', 'ean', 'sku', 'price'])
                ->first();

            if (! $product) {
                return response()->json([
                    'ok' => false,
                    'product' => null,
                ]);
            }

            $productId = (int) $product->getKey();

            $inventoryRow = null;
            if ($storeId > 0) {
                $inventoryRow = DB::table('inventories')
                    ->where('store_id', $storeId)
                    ->where('product_id', $productId)
                    ->select(['quantity', 'last_unit_cost', 'last_purchase_at'])
                    ->first();
            }

            $stockTotal = (int) DB::table('inventories')
                ->join('stores', 'stores.id', '=', 'inventories.store_id')
                ->where('inventories.product_id', $productId)
                ->where('stores.is_active', true)
                ->sum('inventories.quantity');

            $otherStores = $this->stockOtherStores($productId, $storeId);

            // Fallback: if there is old purchase history but inventory doesn't have last cost yet.
            $fallback = null;
            if ($storeId > 0 && ($inventoryRow?->last_unit_cost === null)) {
                $fallback = $this->lastCostFromPurchases($productId, $storeId);
            }

            return response()->json([
                'ok' => true,
                'product' => $this->presentApiProduct(
                    product: $product,
                    storeId: $storeId,
                    inventoryRow: $inventoryRow,
                    stockTotal: $stockTotal,
                    otherStores: $otherStores,
                    fallbackCost: $fallback,
                ),
            ]);
        }

        if ($q === '') {
            return response()->json([
                'ok' => true,
                'products' => [],
            ]);
        }

        $digits = preg_replace('/\\D+/', '', $q) ?? '';

        $products = Product::query()
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
            ->limit($limit)
            ->get(['id', 'name', 'ean', 'sku', 'price']);

        $productIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();

        $inventoryByProductId = [];
        if ($storeId > 0 && $productIds !== []) {
            $rows = DB::table('inventories')
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->select(['product_id', 'quantity', 'last_unit_cost', 'last_purchase_at'])
                ->get();

            foreach ($rows as $row) {
                $inventoryByProductId[(int) $row->product_id] = $row;
            }
        }

        $stockTotalsByProductId = [];
        if ($productIds !== []) {
            $rows = DB::table('inventories')
                ->join('stores', 'stores.id', '=', 'inventories.store_id')
                ->where('stores.is_active', true)
                ->whereIn('inventories.product_id', $productIds)
                ->groupBy('inventories.product_id')
                ->select(['inventories.product_id', DB::raw('SUM(inventories.quantity) as total')])
                ->get();

            foreach ($rows as $row) {
                $stockTotalsByProductId[(int) $row->product_id] = (int) $row->total;
            }
        }

        $presented = [];
        foreach ($products as $p) {
            $pid = (int) $p->getKey();
            $presented[] = $this->presentApiProduct(
                product: $p,
                storeId: $storeId,
                inventoryRow: $inventoryByProductId[$pid] ?? null,
                stockTotal: (int) ($stockTotalsByProductId[$pid] ?? 0),
                otherStores: [],
                fallbackCost: null,
            );
        }

        return response()->json([
            'ok' => true,
            'products' => $presented,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentApiProduct(
        Product $product,
        int $storeId,
        ?object $inventoryRow,
        int $stockTotal,
        array $otherStores,
        ?array $fallbackCost,
    ): array
    {
        $productId = (int) $product->getKey();

        $stock = null;
        if ($inventoryRow && property_exists($inventoryRow, 'quantity')) {
            $stock = (int) $inventoryRow->quantity;
        } elseif ($storeId > 0) {
            $stock = 0;
        }

        $lastUnitCost = null;
        $lastUnitCostAt = null;

        if ($inventoryRow && property_exists($inventoryRow, 'last_unit_cost') && $inventoryRow->last_unit_cost !== null) {
            $lastUnitCost = (string) $inventoryRow->last_unit_cost;
            $lastUnitCostAt = $inventoryRow->last_purchase_at !== null ? (string) $inventoryRow->last_purchase_at : null;
        } elseif ($fallbackCost) {
            $lastUnitCost = $fallbackCost['unit_cost'] ?? null;
            $lastUnitCostAt = $fallbackCost['occurred_at'] ?? null;
        }

        return [
            'id' => $productId,
            'name' => (string) $product->name,
            'ean' => $product->ean !== null ? (string) $product->ean : null,
            'sku' => $product->sku !== null ? (string) $product->sku : null,
            'price' => $product->price !== null ? (string) $product->price : null,
            'last_unit_cost' => $lastUnitCost,
            'last_unit_cost_at' => $lastUnitCostAt,
            'stock' => $stock,
            'stock_total' => $stockTotal,
            'stock_other_stores' => $otherStores,
        ];
    }

    /**
     * @return array<int, array{id:int, name:string, quantity:int}>
     */
    private function stockOtherStores(int $productId, int $storeId): array
    {
        return DB::table('inventories')
            ->join('stores', 'stores.id', '=', 'inventories.store_id')
            ->where('inventories.product_id', $productId)
            ->where('stores.is_active', true)
            ->when($storeId > 0, fn ($q) => $q->where('inventories.store_id', '!=', $storeId))
            ->where('inventories.quantity', '>', 0)
            ->orderByDesc('inventories.quantity')
            ->orderBy('stores.name')
            ->limit(5)
            ->get(['stores.id as id', 'stores.name as name', 'inventories.quantity as quantity'])
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'quantity' => (int) $row->quantity,
            ])
            ->all();
    }

    /**
     * @return array{unit_cost:string, occurred_at:string}|null
     */
    private function lastCostFromPurchases(int $productId, int $storeId): ?array
    {
        $row = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.product_id', $productId)
            ->whereNotNull('purchase_items.unit_cost')
            ->when($storeId > 0, fn ($q) => $q->where('purchases.store_id', $storeId))
            ->orderByDesc('purchases.occurred_at')
            ->orderByDesc('purchase_items.id')
            ->select(['purchase_items.unit_cost as unit_cost', 'purchases.occurred_at as occurred_at'])
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'unit_cost' => (string) $row->unit_cost,
            'occurred_at' => (string) $row->occurred_at,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create', [
            'product' => new Product(),
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')],
            'sku' => ['nullable', 'string', 'max:255'],
            'ean' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'ean' => $validated['ean'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'price' => $validated['price'] ?? null,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        $product->categories()->sync($validated['category_ids'] ?? []);

        $audit->log(
            action: 'product.created',
            auditable: $product,
            before: null,
            after: [
                'id' => (int) $product->getKey(),
                'name' => (string) $product->name,
                'slug' => (string) $product->slug,
                'sku' => $product->sku,
                'ean' => $product->ean,
                'supplier_id' => $product->supplier_id,
                'price' => $product->price !== null ? (string) $product->price : null,
                'is_active' => (bool) $product->is_active,
            ],
        );

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Produto criado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['categories', 'offers', 'supplier', 'inventories.store']);

        return view('admin.products.show', [
            'product' => $product,
            'stores' => Store::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('categories');

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, AuditLogger $audit)
    {
        $before = $product->only([
            'name',
            'slug',
            'sku',
            'ean',
            'supplier_id',
            'short_description',
            'description',
            'image_url',
            'price',
            'requires_prescription',
            'is_active',
            'is_featured',
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product->getKey())],
            'sku' => ['nullable', 'string', 'max:255'],
            'ean' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $product->slug,
            'sku' => $validated['sku'] ?? null,
            'ean' => $validated['ean'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'price' => $validated['price'] ?? null,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        $product->categories()->sync($validated['category_ids'] ?? []);

        $after = $product->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'product.updated',
            auditable: $product,
            before: $before,
            after: $after,
            meta: [
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Produto atualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, AuditLogger $audit)
    {
        $before = $product->only(['id', 'name', 'slug', 'sku', 'ean', 'supplier_id', 'price', 'is_active']);

        $product->delete();

        $audit->log(
            action: 'product.deleted',
            auditable: $product,
            before: $before,
            after: null,
            meta: null,
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produto removido.');
    }
}
