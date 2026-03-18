<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'from_store_id',
        'to_store_id',
        'user_id',
        'reference',
        'status',
        'occurred_at',
        'notes',
        'items_count',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'items_count' => 'integer',
    ];

    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}
