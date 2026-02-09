<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessInvoices
{
    /**
     * Invoices are visible only to Admin and Client; Developer and Sales cannot access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }
        if ($user->isDeveloper() || $user->isSales()) {
            abort(403, 'You do not have access to the Invoices section.');
        }

        return $next($request);
    }
}
