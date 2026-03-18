<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Catalog\GtinSearchClient;
use Illuminate\Http\Request;

class GtinLookupController extends Controller
{
    public function lookup(Request $request, GtinSearchClient $client)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:60'],
        ]);

        $data = $client->lookup((string) $validated['code']);
        if (! $data) {
            return response()->json([
                'ok' => false,
                'data' => null,
                'message' => 'Nenhum dado encontrado para este código.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
