<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Standard Inertia convention: shares data available on every page
 * without each controller having to pass it explicitly. AuthenticatedLayout
 * (resources/js/Layouts/AuthenticatedLayout.jsx) reads `auth.user` from
 * this via usePage().props.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'is_lead_teacher' => $request->user()->is_lead_teacher,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'temp_password' => fn () => $request->session()->get('temp_password'),
            ],
        ];
    }
}
