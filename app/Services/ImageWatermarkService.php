<?php

namespace App\Services;

use App\Models\WatermarkSetting;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageWatermarkService
{
    public function publicUrl(string $path, bool $apply = true): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            return '';
        }

        if (! $apply || ! extension_loaded('gd')) {
            return Storage::disk('public')->url($path);
        }

        return url('/media/wm/'.$this->encode($path));
    }

    public function encode(string $path): string
    {
        $payload = $path.'|'.hash_hmac('sha256', $path, (string) config('app.key'));

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public function decode(string $encoded): ?string
    {
        $raw = base64_decode(strtr($encoded, '-_', '+/'), true);

        if (! is_string($raw) || ! str_contains($raw, '|')) {
            return null;
        }

        [$path, $mac] = explode('|', $raw, 2);

        if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
            return null;
        }

        $expected = hash_hmac('sha256', $path, (string) config('app.key'));

        if (! hash_equals($expected, $mac)) {
            return null;
        }

        return $path;
    }

    public function cachedPath(string $path, ?WatermarkSetting $settings = null): ?string
    {
        $path = ltrim($path, '/');
        $settings ??= WatermarkSetting::current();
        $source = Storage::disk('public')->path($path);

        if (! is_file($source)) {
            return null;
        }

        $destination = $this->cacheFile($path, $settings, $source);

        if (is_file($destination) && filemtime($destination) >= filemtime($source)) {
            return $destination;
        }

        try {
            $this->stampToFile($source, $destination, $settings);
        } catch (Throwable $e) {
            report($e);

            return $source;
        }

        return is_file($destination) ? $destination : $source;
    }

    protected function stampToFile(string $source, string $destination, WatermarkSetting $settings): void
    {
        $binary = @file_get_contents($source);

        if ($binary === false) {
            throw new \RuntimeException('Unable to read image.');
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            throw new \RuntimeException('Unable to parse image.');
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);
        $this->applyStamp($image, $settings);

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagejpeg($image, $destination, 86);
        imagedestroy($image);
    }

    protected function applyStamp(GdImage $image, WatermarkSetting $settings): void
    {
        $pageWidth = imagesx($image);
        $pageHeight = imagesy($image);
        $target = (int) max(48, min($pageWidth * 0.42, max(24, (int) $settings->size) * ($pageWidth / 800)));
        $stamp = $this->makeStamp($settings, $target);

        if (! $stamp) {
            return;
        }

        $stampWidth = imagesx($stamp);
        $stampHeight = imagesy($stamp);
        $diag = (int) max($stampWidth, hypot($stampWidth, $stampHeight));
        $spacing = max((int) $settings->spacing, (int) round($diag * 0.35));
        $step = $diag + $spacing;

        if ($settings->isTiled()) {
            for ($y = (int) (-$diag / 2); $y < $pageHeight + $diag; $y += $step) {
                for ($x = (int) (-$diag / 2); $x < $pageWidth + $diag; $x += $step) {
                    imagecopy($image, $stamp, $x, $y, 0, 0, $stampWidth, $stampHeight);
                }
            }
        } else {
            $x = (int) (($pageWidth - $stampWidth) / 2);
            $y = (int) (($pageHeight - $stampHeight) / 2);
            imagecopy($image, $stamp, $x, $y, 0, 0, $stampWidth, $stampHeight);
        }

        imagedestroy($stamp);
    }

    protected function makeStamp(WatermarkSetting $settings, int $targetWidth): ?GdImage
    {
        $sourcePath = $settings->imageAbsolutePath();
        $base = null;

        if ($sourcePath && is_file($sourcePath)) {
            $binary = @file_get_contents($sourcePath);
            $loaded = $binary !== false ? @imagecreatefromstring($binary) : false;

            if ($loaded instanceof GdImage) {
                $ratio = imagesy($loaded) > 0 ? imagesx($loaded) / imagesy($loaded) : 1;
                $width = $targetWidth;
                $height = max(1, (int) round($width / max($ratio, 0.01)));
                $base = imagecreatetruecolor($width, $height);
                imagealphablending($base, false);
                imagesavealpha($base, true);
                $transparent = imagecolorallocatealpha($base, 0, 0, 0, 127);
                imagefilledrectangle($base, 0, 0, $width, $height, $transparent);
                imagealphablending($base, true);
                imagecopyresampled($base, $loaded, 0, 0, 0, 0, $width, $height, imagesx($loaded), imagesy($loaded));
                imagedestroy($loaded);
            }
        }

        if (! $base) {
            $width = $targetWidth;
            $height = max(32, (int) round($targetWidth * 0.35));
            $base = imagecreatetruecolor($width, $height);
            imagealphablending($base, false);
            imagesavealpha($base, true);
            $transparent = imagecolorallocatealpha($base, 0, 0, 0, 127);
            imagefilledrectangle($base, 0, 0, $width, $height, $transparent);
            imagealphablending($base, true);
            $gold = imagecolorallocatealpha($base, 201, 168, 76, 20);
            $font = 5;
            $text = 'SINO GOOD';
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            imagestring($base, $font, (int) (($width - $textWidth) / 2), (int) (($height - $textHeight) / 2), $text, $gold);
        }

        $this->applyOpacity($base, (int) $settings->opacity);
        $rotated = $this->rotate($base, 45);

        if ($rotated !== $base) {
            imagedestroy($base);
        }

        return $rotated;
    }

    protected function rotate(GdImage $image, float $angle): GdImage
    {
        imagealphablending($image, true);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        $rotated = imagerotate($image, $angle, $transparent);

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);

        return $rotated;
    }

    protected function applyOpacity(GdImage $image, int $percent): void
    {
        $factor = max(0, min(100, $percent)) / 100;
        $width = imagesx($image);
        $height = imagesy($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;
                $newAlpha = (int) min(127, round($alpha + ((127 - $alpha) * (1 - $factor))));
                imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $red, $green, $blue, $newAlpha));
            }
        }
    }

    protected function cacheFile(string $path, WatermarkSetting $settings, string $source): string
    {
        $hash = sha1(implode('|', [
            $path,
            (string) filemtime($source),
            (string) $settings->updated_at,
            (string) $settings->image_path,
            (string) $settings->size,
            (string) $settings->opacity,
            (string) $settings->pattern,
            (string) $settings->spacing,
        ]));

        $directory = storage_path('app/public/wm-cache');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.DIRECTORY_SEPARATOR.$hash.'.jpg';
    }
}
