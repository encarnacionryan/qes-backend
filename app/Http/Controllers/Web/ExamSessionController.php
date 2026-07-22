<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * New feature: teacher hosts an Exam as a browsable/joinable session.
 * Replaces the old class-scoped publish flow — see Exam::sessions() and
 * app/Http/Controllers/Api/V1/SessionController.php for the student side.
 */
class ExamSessionController extends Controller
{
    public function index(Exam $exam)
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Sessions', [
            'exam' => $exam,
            'sessions' => $exam->sessions()->latest()->get(),
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        $this->authorizeOwnership($exam);

        $data = $request->validate([
            'visibility' => ['required', 'in:public,private'],
            'password' => ['required_if:visibility,private', 'nullable', 'string', 'min:4'],
        ]);

        $session = new ExamSession([
            'exam_id' => $exam->id,
            'teacher_id' => $exam->teacher_id,
            'visibility' => $data['visibility'],
            'status' => 'open',
        ]);
        $session->setPassword($data['visibility'] === 'private' ? $data['password'] : null);
        $session->save();

        $exam->update(['status' => 'published']);

        return back()->with('success', 'Exam session started. Students can now find it.');
    }

    public function close(ExamSession $examSession)
    {
        $this->authorizeOwnership($examSession->exam);

        $examSession->update(['status' => 'closed']);

        return back()->with('success', 'Session closed. Scores are now released if configured to wait for close.');
    }

    protected function authorizeOwnership(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->id(), 403);
    }
}
