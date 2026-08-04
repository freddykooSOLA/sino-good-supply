<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name_en',
        'name_zh',
        'name_zh_hant',
        'slug_en',
        'slug_zh',
        'slug_zh_hant',
        'short_desc_en',
        'short_desc_zh',
        'short_desc_zh_hant',
        'full_desc_en',
        'full_desc_zh',
        'full_desc_zh_hant',
        'specs',
        'images',
        'pdf_path',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    public function localizedShortDesc(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->short_desc_zh ?: $this->short_desc_en,
            'zh-hant' => $this->short_desc_zh_hant ?: $this->short_desc_en,
            default => $this->short_desc_en,
        };
    }

    public function localizedFullDesc(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->full_desc_zh ?: $this->full_desc_en,
            'zh-hant' => $this->full_desc_zh_hant ?: $this->full_desc_en,
            default => $this->full_desc_en,
        };
    }

    public function thumbnailUrl(): ?string
    {
        $images = $this->images ?? [];

        if (empty($images[0])) {
            return null;
        }

        return Storage::disk('public')->url($images[0]);
    }

    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function pdfUrl(): ?string
    {
        if (! $this->pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($this->pdf_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFindByLocalizedSlug($query, string $slug, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $column = match ($locale) {
            'zh' => 'slug_zh',
            'zh-hant' => 'slug_zh_hant',
            default => 'slug_en',
        };

        return $query->where($column, $slug);
    }
}
