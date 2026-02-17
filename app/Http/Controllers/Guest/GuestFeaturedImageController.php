<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestFeaturedImageController extends Controller
{
    /**
     * Serve featured project images from storage when public/storage symlink is broken (e.g. on Windows).
     * GET /storage/featured-projects/{filename}
     */
    public function __invoke(string $filename): StreamedResponse
    {
        $filename = basename($filename);
        if (! preg_match('/^[a-zA-Z0-9._-]+\.(png|jpe?g|gif|webp)$/i', $filename)) {
            abort(404);
        }

        $path = 'featured-projects/' . $filename;
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $mime = match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response()->stream(
            function () use ($path) {
                $stream = Storage::disk('public')->readStream($path);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
