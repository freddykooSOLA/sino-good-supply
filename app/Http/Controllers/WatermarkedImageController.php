<?php

namespace App\Http\Controllers;

use App\Services\ImageWatermarkService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WatermarkedImageController extends Controller
{
    public function show(string $encoded, ImageWatermarkService $service): BinaryFileResponse
    {
        $path = $service->decode($encoded);
        abort_unless($path, 404);

        $file = $service->cachedPath($path);
        abort_unless($file && is_file($file), 404);

        return response()->file($file, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
