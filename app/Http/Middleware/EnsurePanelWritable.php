<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelWritable
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isReadOnly()) {
            $route = $request->route()?->getName() ?? '';

            if (! $request->isMethodSafe() || Str::endsWith($route, ['.create', '.edit'])) {
                abort(403, 'Akun kepala sekolah hanya dapat melihat data.');
            }
        }

        return $next($request);
    }
}
