<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'store_id',
        'supplier_id',
        'user_id',
        'reference',
        'nfe_key',
        'nfe_number',
        'nfe_series',
        'xml_hash',
        'xml_path',
        'status',
        'occurred_at',
        'notes',
        'items_count',
        'total_cost',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'items_count' => 'integer',
        'total_cost' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
