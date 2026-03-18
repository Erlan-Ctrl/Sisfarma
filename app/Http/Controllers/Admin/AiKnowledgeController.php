<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiKnowledgeEntry;
use Illuminate\Http\Request;

class AiKnowledgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $entries = AiKnowledgeEntry::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub
                        ->where('title', 'ilike', "%{$q}%")
                        ->orWhere('content', 'ilike', "%{$q}%")
                        ->orWhere('tags', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.knowledge.index', [
            'q' => $q,
            'entries' => $entries,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $entry = new AiKnowledgeEntry([
            'title' => (string) $request->query('title', ''),
        ]);

        return view('admin.knowledge.create', [
            'entry' => $entry,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $entry = AiKnowledgeEntry::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $validated['tags'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.knowledge.edit', $entry)
            ->with('status', 'Entrada de conhecimento criada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AiKnowledgeEntry $entry)
    {
        return view('admin.knowledge.show', [
            'entry' => $entry,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AiKnowledgeEntry $entry)
    {
        return view('admin.knowledge.edit', [
            'entry' => $entry,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AiKnowledgeEntry $entry)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $entry->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $validated['tags'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.knowledge.edit', $entry)
            ->with('status', 'Entrada atualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AiKnowledgeEntry $entry)
    {
        $entry->delete();

        return redirect()
            ->route('admin.knowledge.index')
            ->with('status', 'Entrada removida.');
    }
}
