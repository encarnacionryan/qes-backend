<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-1.5: enforce role-based access control server-side (not just hidden
 * in the UI). Register in bootstrap/app.php as the 'role' alias.
 *
 * Usage: ->middleware('role:teacher') or ->middleware('role:student')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
