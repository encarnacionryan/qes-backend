<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-8.1: restricts account-management routes to the lead teacher, since
 * v1.0 has no separate admin account type. Register as the 'lead_teacher' alias.
 */
class EnsureUserIsLeadTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_lead_teacher) {
            abort(403, 'This action is restricted to the lead teacher.');
        }

        return $next($request);
    }
}
