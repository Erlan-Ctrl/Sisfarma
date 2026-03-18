<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'banner_url',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['offer_price', 'discount_percent', 'position'])
            ->orderBy('offer_product.position');
    }

    protected static function booted(): void
    {
        static::creating(function (self $offer): void {
            if (blank($offer->slug) && filled($offer->title)) {
                $offer->slug = $offer->generateUniqueSlug($offer->title);
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
