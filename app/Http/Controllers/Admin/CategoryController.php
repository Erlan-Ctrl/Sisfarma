<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create', [
            'category' => new Category(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        $audit->log(
            action: 'category.created',
            auditable: $category,
            before: null,
            after: $category->only(['id', 'name', 'slug', 'sort_order', 'is_active']),
        );

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', 'Categoria criada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->loadCount('products');

        return view('admin.categories.show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category, AuditLogger $audit)
    {
        $before = $category->only(['name', 'slug', 'description', 'sort_order', 'is_active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category->getKey())],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $category->slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        $after = $category->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'category.updated',
            auditable: $category,
            before: $before,
            after: $after,
            meta: [
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', 'Categoria atualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, AuditLogger $audit)
    {
        $before = $category->only(['id', 'name', 'slug', 'sort_order', 'is_active']);

        $category->delete();

        $audit->log(
            action: 'category.deleted',
            auditable: $category,
            before: $before,
            after: null,
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Categoria removida.');
    }
}
