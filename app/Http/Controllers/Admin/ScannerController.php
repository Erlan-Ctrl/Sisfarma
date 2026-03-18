<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index(Request $request)
    {
        $codeRaw = trim((string) $request->query('code', ''));
        $code = preg_replace('/\\s+/', '', $codeRaw);
        $digits = preg_replace('/\\D+/', '', $code);

        $product = null;
        if ($code !== '') {
            $product = Product::query()
                ->where(function ($query) use ($code, $digits) {
                    if ($digits !== '') {
                        $query->where('ean', $digits);
                    }

                    $query->orWhere('sku', $code);
                })
                ->with('categories')
                ->first();
        }

        return view('admin.scanner.index', [
            'code' => $codeRaw,
            'digits' => $digits,
            'product' => $product,
        ]);
    }
}

