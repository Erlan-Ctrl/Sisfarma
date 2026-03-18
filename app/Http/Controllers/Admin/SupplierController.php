<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $onlyActive = (bool) $request->boolean('active');

        $suppliers = Supplier::query()
            ->withCount('products')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('cnpj', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->when($onlyActive, fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'q' => $q,
            'onlyActive' => $onlyActive,
        ]);
    }

    public function create()
    {
        return view('admin.suppliers.create', [
            'supplier' => new Supplier(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:30', 'unique:suppliers,cnpj'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'cnpj' => $validated['cnpj'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $audit->log(
            action: 'supplier.created',
            auditable: $supplier,
            before: null,
            after: $supplier->only(['id', 'name', 'cnpj', 'email', 'phone', 'is_active']),
        );

        return redirect()
            ->route('admin.suppliers.edit', $supplier)
            ->with('status', 'Fornecedor cadastrado.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('products');

        return view('admin.suppliers.show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier, AuditLogger $audit)
    {
        $before = $supplier->only(['name', 'cnpj', 'phone', 'email', 'notes', 'is_active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:30', Rule::unique('suppliers', 'cnpj')->ignore($supplier->getKey())],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update([
            'name' => $validated['name'],
            'cnpj' => $validated['cnpj'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $after = $supplier->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'supplier.updated',
            auditable: $supplier,
            before: $before,
            after: $after,
            meta: [
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.suppliers.edit', $supplier)
            ->with('status', 'Fornecedor atualizado.');
    }

    public function destroy(Supplier $supplier, AuditLogger $audit)
    {
        $before = $supplier->only(['id', 'name', 'cnpj', 'email', 'phone', 'is_active']);

        $supplier->forceFill(['is_active' => false])->save();

        $after = $supplier->fresh()?->only(['id', 'name', 'cnpj', 'email', 'phone', 'is_active']) ?: null;

        $audit->log(
            action: 'supplier.disabled',
            auditable: $supplier,
            before: $before,
            after: $after,
        );

        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', 'Fornecedor desativado.');
    }
}
