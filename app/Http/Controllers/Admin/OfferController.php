<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offers = Offer::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.offers.index', [
            'offers' => $offers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.offers.create', [
            'offer' => new Offer(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->limit(200)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('offers', 'slug')],
            'description' => ['nullable', 'string'],
            'banner_url' => ['nullable', 'string', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $offer = Offer::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'banner_url' => $validated['banner_url'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $offer->products()->sync($validated['product_ids'] ?? []);

        $audit->log(
            action: 'offer.created',
            auditable: $offer,
            before: null,
            after: $offer->only(['id', 'title', 'slug', 'starts_at', 'ends_at', 'is_active']),
            meta: [
                'product_ids' => array_values(array_map('intval', $validated['product_ids'] ?? [])),
            ],
        );

        return redirect()
            ->route('admin.offers.edit', $offer)
            ->with('status', 'Oferta criada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        $offer->load('products');

        return view('admin.offers.show', [
            'offer' => $offer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        $offer->load('products');

        return view('admin.offers.edit', [
            'offer' => $offer,
            'products' => Product::query()->where('is_active', true)->orderBy('name')->limit(200)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer, AuditLogger $audit)
    {
        $before = $offer->only(['title', 'slug', 'description', 'banner_url', 'starts_at', 'ends_at', 'is_active']);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('offers', 'slug')->ignore($offer->getKey())],
            'description' => ['nullable', 'string'],
            'banner_url' => ['nullable', 'string', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $offer->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? $offer->slug,
            'description' => $validated['description'] ?? null,
            'banner_url' => $validated['banner_url'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $offer->products()->sync($validated['product_ids'] ?? []);

        $after = $offer->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'offer.updated',
            auditable: $offer,
            before: $before,
            after: $after,
            meta: [
                'product_ids' => array_values(array_map('intval', $validated['product_ids'] ?? [])),
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.offers.edit', $offer)
            ->with('status', 'Oferta atualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer, AuditLogger $audit)
    {
        $before = $offer->only(['id', 'title', 'slug', 'starts_at', 'ends_at', 'is_active']);

        $offer->delete();

        $audit->log(
            action: 'offer.deleted',
            auditable: $offer,
            before: $before,
            after: null,
        );

        return redirect()
            ->route('admin.offers.index')
            ->with('status', 'Oferta removida.');
    }
}
