<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    protected $fillable = [
        'name_en',
        'name_zh',
        'name_zh_hant',
        'slug_en',
        'slug_zh',
        'slug_zh_hant',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(Series::class);
    }

    public function previewProduct(): HasOne
    {
        return $this->hasOne(Product::class)->ofMany(
            [
                'sort_order' => 'min',
                'id' => 'min',
            ],
            function ($query) {
                $query->where('is_active', true);
            }
        );
    }

    public function previewSeries(): HasOne
    {
        return $this->hasOne(Series::class)->ofMany(
            [
                'sort_order' => 'min',
                'id' => 'min',
            ],
            function ($query) {
                $query->where('is_active', true);
            }
        );
    }

    public function previewImageUrl(): ?string
    {
        return $this->previewSeries?->thumbnailUrl()
            ?? $this->previewProduct?->thumbnailUrl();
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->name_zh ?: $this->name_en,
            'zh-hant' => $this->name_zh_hant ?: $this->name_en,
            default => $this->name_en,
        };
    }

    public function localizedSlug(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->slug_zh ?: $this->slug_en,
            'zh-hant' => $this->slug_zh_hant ?: $this->slug_en,
            default => $this->slug_en,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
