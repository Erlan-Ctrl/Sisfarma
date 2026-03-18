<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'user_id',
        'type',
        'delta',
        'quantity_before',
        'quantity_after',
        'reason',
        'note',
        'occurred_at',
        'meta',
    ];

    protected $casts = [
        'delta' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

