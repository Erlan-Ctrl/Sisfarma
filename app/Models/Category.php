<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('position')
            ->orderBy('category_product.position');
    }

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = $category->generateUniqueSlug($category->name);
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
