<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'ean',
        'supplier_id',
        'short_description',
        'description',
        'image_url',
        'price',
        'requires_prescription',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('position')
            ->orderBy('category_product.position');
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class)
            ->withPivot(['offer_price', 'discount_percent', 'position'])
            ->orderBy('offer_product.position');
    }

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
        static::creating(function (self $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = $product->generateUniqueSlug($product->name);
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
