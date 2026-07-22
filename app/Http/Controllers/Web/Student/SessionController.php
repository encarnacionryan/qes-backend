<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * PWA update: students now use the same Inertia web app as teachers
 * (installable on desktop/tablet/phone) instead of a separate native app.
 * Mirrors the logic in App\Http\Controllers\Api\V1\SessionController, but
 * session-authenticated and Inertia-rendered rather than token/JSON.
 */
class SessionController extends Controller
{
    public function index()
    {
        $sessions = ExamSession::where('status', 'open')
            ->with(['exam:id,title,time_limit_minutes,total_points', 'teacher:id,name'])
            ->latest()
            ->get()
            ->map(fn (ExamSession $session) => [
                'id' => $session->id,
                'visibility' => $session->visibility,
                'requires_password' => $session->isPrivate(),
                'exam' => $session->exam,
                'teacher' => $session->teacher,
            ]);

        return Inertia::render('Student/Sessions/Browse', ['sessions' => $sessions]);
    }

    public function join(Request $request, ExamSession $examSession)
    {
        abort_unless($examSession->isOpen(), 422, 'This exam session is no longer open.');

        $data = $request->validate(['password' => ['nullable', 'string']]);

        if (! $examSession->checkPassword($data['password'] ?? null)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $studentId = $request->user()->id;
        $exam = $examSession->exam;

        $attemptCount = $examSession->submissions()->where('student_id', $studentId)->count();
        abort_if($attemptCount > 0 && ! $exam->allow_retake, 422, 'You have already joined this session.');

        $submission = $examSession->submissions()->create([
            'exam_id' => $exam->id,
            'student_id' => $studentId,
            'started_at' => now(),
            'status' => 'in_progress',
            'attempt_number' => $attemptCount + 1,
        ]);

        return redirect()->route('student.submissions.take', $submission);
    }
}
