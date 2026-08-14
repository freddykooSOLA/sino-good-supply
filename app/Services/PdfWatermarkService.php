<?php

namespace App\Services;

use App\Models\WatermarkSetting;
use setasign\Fpdi\Tcpdf\Fpdi;
use Throwable;

class PdfWatermarkService
{
    public function generateWatermarkedPdf(string $originalPdfPath, ?WatermarkSetting $settings = null): string
    {
        $settings ??= WatermarkSetting::current();
        $originalPdfPath = $this->normalizePath($originalPdfPath);

        if (! is_file($originalPdfPath)) {
            throw new \RuntimeException('PDF file not found.');
        }

        $cachePath = $this->cachePath($originalPdfPath, $settings);

        if (is_file($cachePath) && filemtime($cachePath) >= filemtime($originalPdfPath)) {
            return $cachePath;
        }

        try {
            $this->stampWithFpdi($originalPdfPath, $cachePath, $settings);
        } catch (Throwable $e) {
            report($e);
            copy($originalPdfPath, $cachePath);
        }

        return $cachePath;
    }

    protected function stampWithFpdi(string $source, string $destination, WatermarkSetting $settings): void
    {
        $pdf = new class('P', 'pt') extends Fpdi
        {
            public function Header(): void {}

            public function Footer(): void {}
        };

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($source);
        $stamp = $this->prepareStampImage($settings);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template, 0, 0, $size['width'], $size['height'], true);
            $this->applyWatermark($pdf, $size['width'], $size['height'], $settings, $stamp);
        }

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->Output($destination, 'F');

        if ($stamp && str_starts_with(basename($stamp['path']), 'wm-') && is_file($stamp['path'])) {
            @unlink($stamp['path']);
        }
    }

    /**
     * @param  array{path: string, width: int, height: int}|null  $stamp
     */
    protected function applyWatermark(Fpdi $pdf, float $pageWidth, float $pageHeight, WatermarkSetting $settings, ?array $stamp): void
    {
        $opacity = max(0, min(100, (int) $settings->opacity)) / 100;
        $pdf->SetAlpha($opacity);

        if ($stamp) {
            $this->stampImage($pdf, $pageWidth, $pageHeight, $settings, $stamp);
        } else {
            $this->stampText($pdf, $pageWidth, $pageHeight, $settings);
        }

        $pdf->SetAlpha(1);
    }

    /**
     * @param  array{path: string, width: int, height: int}  $stamp
     */
    protected function stampImage(Fpdi $pdf, float $pageWidth, float $pageHeight, WatermarkSetting $settings, array $stamp): void
    {
        $target = max(24, (int) $settings->size);
        $ratio = $stamp['height'] > 0 ? ($stamp['width'] / $stamp['height']) : 1;
        $width = $target;
        $height = $ratio > 0 ? $target / $ratio : $target;

        if ($settings->isTiled()) {
            $spacing = max(10, (int) $settings->spacing);
            $stepX = $width + $spacing;
            $stepY = $height + $spacing;

            for ($y = -$height; $y < $pageHeight + $height; $y += $stepY) {
                for ($x = -$width; $x < $pageWidth + $width; $x += $stepX) {
                    $this->drawRotatedImage($pdf, $stamp['path'], $x, $y, $width, $height);
                }
            }

            return;
        }

        $x = ($pageWidth - $width) / 2;
        $y = ($pageHeight - $height) / 2;
        $this->drawRotatedImage($pdf, $stamp['path'], $x, $y, $width, $height);
    }

    protected function stampText(Fpdi $pdf, float $pageWidth, float $pageHeight, WatermarkSetting $settings): void
    {
        $text = 'SINO GOOD';
        $fontSize = max(18, (int) $settings->size * 0.35);
        $pdf->SetFont('helvetica', 'B', $fontSize);
        $pdf->SetTextColor(180, 180, 180);
        $textWidth = $pdf->GetStringWidth($text);

        if ($settings->isTiled()) {
            $spacing = max(20, (int) $settings->spacing);
            $stepX = $textWidth + $spacing;
            $stepY = $fontSize + $spacing;

            for ($y = 20; $y < $pageHeight; $y += $stepY) {
                for ($x = 10; $x < $pageWidth; $x += $stepX) {
                    $this->drawRotatedText($pdf, $text, $x, $y, $fontSize);
                }
            }

            return;
        }

        $x = ($pageWidth - $textWidth) / 2;
        $y = $pageHeight / 2;
        $this->drawRotatedText($pdf, $text, $x, $y, $fontSize);
    }

    protected function drawRotatedImage(Fpdi $pdf, string $path, float $x, float $y, float $width, float $height): void
    {
        $cx = $x + ($width / 2);
        $cy = $y + ($height / 2);
        $pdf->StartTransform();
        $pdf->Rotate(45, $cx, $cy);
        $pdf->Image($path, $x, $y, $width, $height, '', '', '', false, 300, '', false, false, 0);
        $pdf->StopTransform();
    }

    protected function drawRotatedText(Fpdi $pdf, string $text, float $x, float $y, float $fontSize): void
    {
        $pdf->StartTransform();
        $pdf->Rotate(45, $x, $y);
        $pdf->SetXY($x, $y - $fontSize);
        $pdf->Cell(0, $fontSize, $text, 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->StopTransform();
    }

    /**
     * @return array{path: string, width: int, height: int}|null
     */
    protected function prepareStampImage(WatermarkSetting $settings): ?array
    {
        $source = $settings->imageAbsolutePath();
        if (! $source || ! extension_loaded('gd')) {
            return $source && is_file($source)
                ? ['path' => $source, 'width' => 400, 'height' => 400]
                : null;
        }

        $binary = @file_get_contents($source);
        if ($binary === false) {
            return null;
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return ['path' => $source, 'width' => 400, 'height' => 400];
        }

        imagesavealpha($image, true);
        $width = imagesx($image);
        $height = imagesy($image);

        $temp = storage_path('app/private/pdf-cache/wm-'.uniqid('', true).'.png');
        $directory = dirname($temp);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagepng($image, $temp);
        imagedestroy($image);

        return [
            'path' => $temp,
            'width' => $width,
            'height' => $height,
        ];
    }

    protected function cachePath(string $originalPdfPath, WatermarkSetting $settings): string
    {
        $hash = sha1(implode('|', [
            $originalPdfPath,
            (string) filemtime($originalPdfPath),
            (string) $settings->updated_at,
            (string) $settings->image_path,
            (string) $settings->size,
            (string) $settings->opacity,
            (string) $settings->pattern,
            (string) $settings->spacing,
        ]));

        $directory = storage_path('app/private/pdf-cache');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.DIRECTORY_SEPARATOR.$hash.'.pdf';
    }

    protected function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
