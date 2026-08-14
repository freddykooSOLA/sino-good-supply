<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Series extends Model
{
    protected $table = 'series';

    protected $fillable = [
        'category_id',
        'name_en',
        'name_zh',
        'name_zh_hant',
        'slug_en',
        'slug_zh',
        'slug_zh_hant',
        'intro_en',
        'intro_zh',
        'intro_zh_hant',
        'images',
        'pdf_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    public function localizedIntro(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->intro_zh ?: $this->intro_en,
            'zh-hant' => $this->intro_zh_hant ?: $this->intro_en,
            default => $this->intro_en,
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

    public function hasPdf(): bool
    {
        return filled($this->pdf_path) && Storage::disk('local')->exists($this->pdf_path);
    }

    public function pdfAbsolutePath(): ?string
    {
        if (! $this->hasPdf()) {
            return null;
        }

        return Storage::disk('local')->path($this->pdf_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFindByAnySlug($query, string $slug)
    {
        return $query->where(function ($q) use ($slug) {
            $q->where('slug_en', $slug)
                ->orWhere('slug_zh', $slug)
                ->orWhere('slug_zh_hant', $slug);
        });
    }
}
