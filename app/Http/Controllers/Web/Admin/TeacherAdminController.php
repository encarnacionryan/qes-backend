<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Sprint 8: User Management & Hardening (QES-47 to QES-50).
 * Restricted to the lead teacher via the `lead_teacher` middleware (see
 * routes/web.php and EnsureUserIsLeadTeacher).
 */
class TeacherAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Teachers', [
            'teachers' => User::where('role', 'teacher')->get(),
        ]);
    }

    public function disable(User $teacher) // FR-8.2
    {
        $teacher->update(['is_active' => false]);

        \App\Models\AccountActionLog::create([
            'actor_id' => auth()->id(),
            'target_user_id' => $teacher->id,
            'action' => 'account_disabled',
        ]);

        return back()->with('success', 'Teacher account disabled.');
    }

    public function resetPassword(User $user) // FR-8.3
    {
        $tempPassword = Str::random(10);
        $user->update(['password' => Hash::make($tempPassword)]);

        \App\Models\AccountActionLog::create([
            'actor_id' => auth()->id(),
            'target_user_id' => $user->id,
            'action' => 'password_reset',
        ]);

        // No email/internet dependency (SRS 2.5) — show the temp password
        // to the lead teacher directly so they can relay it in person.
        return back()->with('temp_password', $tempPassword);
    }
}
