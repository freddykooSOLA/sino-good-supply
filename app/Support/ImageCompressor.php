<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class ImageCompressor
{
    /**
     * Store an uploaded image resized/compressed to stay under $maxKilobytes.
     */
    public static function storeUnderLimit(
        TemporaryUploadedFile $file,
        string $directory = 'products',
        int $maxKilobytes = 200,
        string $disk = 'public',
        int $maxDimension = 1600,
    ): string {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('服务器未启用 GD 扩展，无法压缩图片。');
        }

        $binary = file_get_contents($file->getRealPath());
        if ($binary === false) {
            throw new RuntimeException('无法读取上传的图片。');
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            throw new RuntimeException('无法解析上传的图片，请使用 JPG / PNG / WEBP。');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height, 1));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $maxBytes = $maxKilobytes * 1024;
        $encoded = null;
        $extension = 'jpg';

        // Prefer WebP when available (smaller), otherwise JPEG quality ladder.
        if (function_exists('imagewebp')) {
            foreach ([82, 72, 62, 52, 42, 32] as $quality) {
                ob_start();
                imagewebp($canvas, null, $quality);
                $candidate = ob_get_clean();
                if ($candidate !== false && strlen($candidate) <= $maxBytes) {
                    $encoded = $candidate;
                    $extension = 'webp';
                    break;
                }
                $encoded = $candidate;
                $extension = 'webp';
            }
        }

        if ($encoded === null || strlen($encoded) > $maxBytes) {
            foreach ([85, 75, 65, 55, 45, 35, 25] as $quality) {
                ob_start();
                imagejpeg($canvas, null, $quality);
                $candidate = ob_get_clean();
                if ($candidate !== false && strlen($candidate) <= $maxBytes) {
                    $encoded = $candidate;
                    $extension = 'jpg';
                    break;
                }
                $encoded = $candidate;
                $extension = 'jpg';
            }
        }

        // Still too large: shrink dimensions further and recompress.
        $attempts = 0;
        while ($encoded !== false && strlen($encoded) > $maxBytes && $attempts < 6) {
            $attempts++;
            $targetWidth = max(1, (int) round($targetWidth * 0.8));
            $targetHeight = max(1, (int) round($targetHeight * 0.8));
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
            $bg = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $bg);
            imagecopyresampled($resized, $canvas, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($canvas), imagesy($canvas));
            imagedestroy($canvas);
            $canvas = $resized;

            ob_start();
            if ($extension === 'webp' && function_exists('imagewebp')) {
                imagewebp($canvas, null, 40);
            } else {
                imagejpeg($canvas, null, 40);
                $extension = 'jpg';
            }
            $encoded = ob_get_clean();
        }

        imagedestroy($canvas);

        if ($encoded === false || $encoded === null || $encoded === '') {
            throw new RuntimeException('图片压缩失败。');
        }

        if (strlen($encoded) > $maxBytes) {
            throw new RuntimeException("图片无法压缩到 {$maxKilobytes}KB 以下，请换一张更小的图。");
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk($disk)->put($path, $encoded);

        return $path;
    }
}
