<?php

namespace App\Support;

class Media
{
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $request = request();
        $base = $request && $request->getHost() ? $request->getSchemeAndHttpHost() : url('/');

        return rtrim($base, '/').'/media/'.ltrim($path, '/');
    }
}
