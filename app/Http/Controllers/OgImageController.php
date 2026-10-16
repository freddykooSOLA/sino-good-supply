<?php

namespace App\Http\Controllers;

use App\Services\OgImageService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OgImageController extends Controller
{
    public function show(string $token, OgImageService $ogImages): BinaryFileResponse
    {
        $response = $ogImages->respond($token);
        abort_if($response === null, 404);

        return $response;
    }
}
