<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores = Store::query()
            ->orderBy('city')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.stores.index', [
            'stores' => $stores,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.stores.create', [
            'store' => new Store(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('stores', 'slug')],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:150'],
            'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $store = Store::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'email' => $validated['email'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'street' => $validated['street'] ?? null,
            'number' => $validated['number'] ?? null,
            'complement' => $validated['complement'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $audit->log(
            action: 'store.created',
            auditable: $store,
            before: null,
            after: $store->only(['id', 'name', 'slug', 'city', 'state', 'is_active']),
        );

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', 'Loja criada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        return view('admin.stores.show', [
            'store' => $store,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        return view('admin.stores.edit', [
            'store' => $store,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Store $store, AuditLogger $audit)
    {
        $before = $store->only([
            'name',
            'slug',
            'phone',
            'whatsapp',
            'email',
            'zip_code',
            'state',
            'city',
            'district',
            'street',
            'number',
            'complement',
            'latitude',
            'longitude',
            'opening_hours',
            'is_active',
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('stores', 'slug')->ignore($store->getKey())],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:150'],
            'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $store->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $store->slug,
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'email' => $validated['email'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'street' => $validated['street'] ?? null,
            'number' => $validated['number'] ?? null,
            'complement' => $validated['complement'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $after = $store->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'store.updated',
            auditable: $store,
            before: $before,
            after: $after,
            meta: [
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', 'Loja atualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store, AuditLogger $audit)
    {
        $before = $store->only(['id', 'name', 'slug', 'city', 'state', 'is_active']);

        $store->delete();

        $audit->log(
            action: 'store.deleted',
            auditable: $store,
            before: $before,
            after: null,
        );

        return redirect()
            ->route('admin.stores.index')
            ->with('status', 'Loja removida.');
    }
}
