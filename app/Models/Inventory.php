<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
        'min_quantity',
        'last_unit_cost',
        'last_purchase_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'last_unit_cost' => 'decimal:2',
        'last_purchase_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
