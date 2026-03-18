<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $store): void {
            if (blank($store->slug) && filled($store->name)) {
                $store->slug = $store->generateUniqueSlug($store->name);
            }
        });
    }

    public function generateUniqueSlug(string $source): string
    {
        $base = Str::slug($source);
        $slug = $base !== '' ? $base : Str::random(8);
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
