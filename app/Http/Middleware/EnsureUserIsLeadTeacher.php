<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


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
