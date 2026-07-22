<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Sprint 1: QES-7 (register), QES-8 (login), QES-12 (logout).
 * Bodies below are minimal working implementations, not final stubs —
 * enough to unblock `php artisan migrate`/`serve` and give you something
 * to build the actual UI against in Sprint 1.
 */
class AuthController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $default = $user->role === 'teacher' ? route('dashboard') : route('student.sessions.index');

        return redirect()->intended($default);
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:teacher,student'], // PWA update: both roles register here now
        ]);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            // FR-8.1: first teacher becomes the lead teacher.
            'is_lead_teacher' => $data['role'] === 'teacher'
                && ! \App\Models\User::where('role', 'teacher')->exists(),
        ]);

        Auth::login($user);

        return redirect($user->role === 'teacher' ? route('dashboard') : route('student.sessions.index'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
