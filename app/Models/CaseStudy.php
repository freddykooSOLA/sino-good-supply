<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'title_en',
        'title_zh',
        'title_zh_hant',
        'description_en',
        'description_zh',
        'description_zh_hant',
        'youtube_url',
        'youtube_id',
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

    protected static function booted(): void
    {
        static::saving(function (CaseStudy $case) {
            $case->youtube_id = static::extractYoutubeId($case->youtube_url);
        });
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', trim($url))) {
            return trim($url);
        }

        return null;
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->title_zh ?: $this->title_en,
            'zh-hant' => $this->title_zh_hant ?: $this->title_en,
            default => $this->title_en,
        };
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return match ($locale) {
            'zh' => $this->description_zh ?: $this->description_en,
            'zh-hant' => $this->description_zh_hant ?: $this->description_en,
            default => $this->description_en,
        };
    }

    public function embedUrl(): ?string
    {
        if (! $this->youtube_id) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$this->youtube_id;
    }

    public function thumbnailUrl(): ?string
    {
        if (! $this->youtube_id) {
            return null;
        }

        return 'https://img.youtube.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
