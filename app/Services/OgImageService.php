<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OgImageService
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    public function brandDefaultUrl(): string
    {
        $slides = Setting::getValue('hero_slides', []) ?: [];
        $first = is_array($slides[0] ?? null) ? $slides[0] : null;
        if ($first && ! empty($first['image'])) {
            $url = $this->urlForStoragePath((string) $first['image']);
            if ($url) {
                return $url;
            }
        }

        $contact = site_contact();
        if (! empty($contact['media_image'])) {
            $url = $this->urlForStoragePath((string) $contact['media_image']);
            if ($url) {
                return $url;
            }
        }

        return $this->urlForStoragePath('series/demo/series-1.jpg') ?? url('/favicon.ico');
    }

    public function urlForStoragePath(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return $relativePath;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($relativePath)) {
            return null;
        }

        $absolute = $disk->path($relativePath);
        $token = $this->tokenForFile($absolute);
        $targetRelative = "og-images/{$token}.jpg";
        $targetAbsolute = $disk->path($targetRelative);

        if (! is_file($targetAbsolute) || filemtime($targetAbsolute) < filemtime($absolute)) {
            $disk->makeDirectory('og-images');
            if (! $this->renderCrop($absolute, $targetAbsolute)) {
                return media_url($relativePath) ? url(media_url($relativePath)) : null;
            }
        }

        return url("/og/{$token}.jpg");
    }

    public function respond(string $token): ?BinaryFileResponse
    {
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }

        $path = Storage::disk('public')->path("og-images/{$token}.jpg");
        if (! is_file($path)) {
            return null;
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * @return array{width: int, height: int}
     */
    public function dimensionsForUrl(string $ogUrl): array
    {
        if (preg_match('#/og/([a-f0-9]{40})\.jpg$#', $ogUrl, $matches)) {
            $path = Storage::disk('public')->path('og-images/'.$matches[1].'.jpg');
            if (is_file($path)) {
                $size = @getimagesize($path);
                if (is_array($size)) {
                    return [
                        'width' => (int) ($size[0] ?? self::WIDTH),
                        'height' => (int) ($size[1] ?? self::HEIGHT),
                    ];
                }
            }
        }

        return ['width' => self::WIDTH, 'height' => self::HEIGHT];
    }

    protected function tokenForFile(string $absolutePath): string
    {
        $mtime = is_file($absolutePath) ? (string) filemtime($absolutePath) : '0';

        return sha1($absolutePath.'|'.$mtime);
    }

    protected function renderCrop(string $sourceAbsolute, string $targetAbsolute): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $info = @getimagesize($sourceAbsolute);
        if (! is_array($info)) {
            return false;
        }

        $mime = $info['mime'] ?? '';
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourceAbsolute),
            'image/png' => @imagecreatefrompng($sourceAbsolute),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourceAbsolute) : false,
            default => false,
        };

        if (! $src) {
            return false;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW < 1 || $srcH < 1) {
            imagedestroy($src);

            return false;
        }

        $targetRatio = self::WIDTH / self::HEIGHT;
        $srcRatio = $srcW / $srcH;

        if ($srcRatio > $targetRatio) {
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetRatio);
        } else {
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetRatio);
        }

        $srcX = (int) max(0, floor(($srcW - $cropW) / 2));
        $srcY = (int) max(0, floor(($srcH - $cropH) / 2));

        $scale = min(self::WIDTH / $cropW, self::HEIGHT / $cropH, 1.0);
        $outW = max(1, (int) round($cropW * $scale));
        $outH = max(1, (int) round($cropH * $scale));

        $dest = imagecreatetruecolor($outW, $outH);
        imagecopyresampled($dest, $src, 0, 0, $srcX, $srcY, $outW, $outH, $cropW, $cropH);
        imagedestroy($src);

        $ok = imagejpeg($dest, $targetAbsolute, 85);
        imagedestroy($dest);

        return $ok;
    }
}
