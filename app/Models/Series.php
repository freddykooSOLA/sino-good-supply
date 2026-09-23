<?php

namespace App\Models;

use App\Services\ImageWatermarkService;
use App\Support\StoredFilePath;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'watermark_images',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'watermark_images' => 'boolean',
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
        return $this->imageUrls()[0] ?? null;
    }

    public function imageUrls(): array
    {
        $apply = (bool) $this->watermark_images;

        return collect($this->images ?? [])
            ->map(fn ($path) => StoredFilePath::normalize($path))
            ->filter()
            ->map(fn (string $path) => app(ImageWatermarkService::class)->publicUrl($path, $apply))
            ->values()
            ->all();
    }

    /**
     * @return array{disk: string, path: string}|null
     */
    public function pdfStorageLocation(): ?array
    {
        $path = StoredFilePath::normalize($this->getAttributes()['pdf_path'] ?? $this->pdf_path);

        if (! $path) {
            return null;
        }

        $candidates = array_unique([
            $path,
            'series-pdfs/'.basename($path),
            'pdfs/'.basename($path),
        ]);

        foreach ($candidates as $candidate) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($candidate)) {
                    return ['disk' => $disk, 'path' => $candidate];
                }
            }
        }

        return null;
    }

    public function hasPdf(): bool
    {
        return $this->pdfStorageLocation() !== null;
    }

    public function pdfAbsolutePath(): ?string
    {
        $location = $this->pdfStorageLocation();

        if (! $location) {
            return null;
        }

        return Storage::disk($location['disk'])->path($location['path']);
    }

    protected function pdfPath(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => StoredFilePath::normalize($value),
            set: fn ($value) => StoredFilePath::normalize($value),
        );
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
