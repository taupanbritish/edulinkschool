<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        abort_if(str_contains($path, '..') || ! preg_match('~^(photos|videos|documents)/~', $path), 404);
        abort_if(str_starts_with($path, 'documents/') && ! str_ends_with(strtolower($path), '.pdf'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        $response = response()->file($disk->path($path), [
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=604800',
        ]);

        if (str_ends_with(strtolower($path), '.ico')) {
            $response->headers->set('Content-Type', 'image/x-icon');
        }

        return $response;
    }
}
