<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isClient()) {
            abort(403, 'Client portal access required.');
        }

        if (! $request->user()->client) {
            abort(403, 'Client account not linked.');
        }

        return $next($request);
    }
}
