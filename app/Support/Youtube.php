<?php

namespace App\Support;

class Youtube
{
    public static function embedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~youtube(?:-nocookie)?\.com/(?:watch\?[^#]*v=|embed/|shorts/|live/)([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://www.youtube-nocookie.com/embed/'.$matches[1];
            }
        }

        return null;
    }
}
