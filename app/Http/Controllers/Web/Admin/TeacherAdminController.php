<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TeacherAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Teachers', [
            'teachers' => User::where('role', 'teacher')->orderBy('name')->get(),
            'students' => User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email', 'is_active']),
            'logs' => AccountActionLog::with('actor:id,name', 'targetUser:id,name')
                ->latest()
                ->take(20)
                ->get(),
        ]);
    }

    public function store(Request $request) 
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $tempPassword = Str::random(10);

        $teacher = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'role' => 'teacher',
            'is_lead_teacher' => false,
        ]);

        $this->logAction($teacher, 'account_created');
        return back()->with('temp_password', $tempPassword);
    }

    public function disable(User $teacher) 
    {
        if ($teacher->id === auth()->id()) {
            return back()->with('error', "You can't disable your own account.");
        }

        $teacher->update(['is_active' => false]);
        $this->logAction($teacher, 'account_disabled');

        return back()->with('success', 'Teacher account disabled.');
    }

    public function enable(User $teacher) 
    {
        $teacher->update(['is_active' => true]);
        $this->logAction($teacher, 'account_enabled');

        return back()->with('success', 'Teacher account re-enabled.');
    }

    public function destroy(User $teacher) 
    {
        if ($teacher->id === auth()->id()) {
            return back()->with('error', "You can't remove your own account.");
        }
        if ($teacher->is_lead_teacher) {
            return back()->with('error', "The lead teacher account can't be removed.");
        }

        $this->logAction($teacher, 'account_removed');

        $teacher->delete();

        return back()->with('success', 'Teacher account removed.');
    }

    public function resetPassword(User $user) 
    {
        $tempPassword = Str::random(10);
        $user->update(['password' => Hash::make($tempPassword)]);
        $this->logAction($user, 'password_reset');

        return back()->with('temp_password', $tempPassword);
    }

    protected function logAction(User $target, string $action): void
    {
        AccountActionLog::create([
            'actor_id' => auth()->id(),
            'target_user_id' => $target->id,
            'target_label' => "{$target->name} ({$target->email})",
            'action' => $action,
        ]);
    }
}
