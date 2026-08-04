<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CaseStudy extends Model
{
    public const VIDEO_YOUTUBE = 'youtube';

    public const VIDEO_UPLOAD = 'upload';

    protected $fillable = [
        'title_en',
        'title_zh',
        'title_zh_hant',
        'description_en',
        'description_zh',
        'description_zh_hant',
        'video_type',
        'youtube_url',
        'youtube_id',
        'video_path',
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
            if (($case->video_type ?: self::VIDEO_YOUTUBE) === self::VIDEO_YOUTUBE) {
                $case->video_type = self::VIDEO_YOUTUBE;
                $case->youtube_id = static::extractYoutubeId($case->youtube_url);

                $previousVideo = $case->getOriginal('video_path');
                if ($previousVideo) {
                    Storage::disk('public')->delete($previousVideo);
                }

                $case->video_path = null;
            } else {
                $case->video_type = self::VIDEO_UPLOAD;
                $case->youtube_url = null;
                $case->youtube_id = null;
            }
        });

        static::updating(function (CaseStudy $case) {
            if ($case->isDirty('video_path') && $case->getOriginal('video_path') && $case->getOriginal('video_path') !== $case->video_path) {
                Storage::disk('public')->delete($case->getOriginal('video_path'));
            }
        });

        static::deleting(function (CaseStudy $case) {
            if ($case->video_path) {
                Storage::disk('public')->delete($case->video_path);
            }
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

    public function isYoutube(): bool
    {
        return ($this->video_type ?: self::VIDEO_YOUTUBE) === self::VIDEO_YOUTUBE;
    }

    public function isUploadedVideo(): bool
    {
        return $this->video_type === self::VIDEO_UPLOAD && filled($this->video_path);
    }

    public function embedUrl(): ?string
    {
        if (! $this->isYoutube() || ! $this->youtube_id) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$this->youtube_id;
    }

    public function uploadedVideoUrl(): ?string
    {
        if (! $this->isUploadedVideo()) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->isYoutube() && $this->youtube_id) {
            return 'https://img.youtube.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
