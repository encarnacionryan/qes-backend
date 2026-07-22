<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Sprint 3: Exam Creation & Management (QES-20 to QES-26).
 * leaderboard()/analytics() are placeholders — real implementations land
 * in Sprint 6 (QES-38+) and Sprint 7 (QES-43+) respectively.
 */
class ExamController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Exams/Index', [
            'exams' => $request->user()->examsCreated()->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Exams/Create');
    }

    public function store(Request $request) // FR-3.1
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['required', 'integer', 'min:1'],
        ]);

        $exam = $request->user()->examsCreated()->create($data);

        return redirect()->route('exams.edit', $exam)->with('success', 'Exam created — add questions next.');
    }

    public function show(Exam $exam)
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Show', ['exam' => $exam->load('questions.choices', 'classes')]);
    }

    public function edit(Exam $exam) // FR-3.2, FR-3.3, FR-3.4
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);

        return Inertia::render('Exams/Edit', ['exam' => $exam->load('questions.choices')]);
    }

    public function update(Request $request, Exam $exam) // FR-3.4
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['required', 'integer', 'min:1'],
            'show_score_immediately' => ['boolean'],
            'allow_retake' => ['boolean'],
            'anonymize_leaderboard' => ['boolean'],
        ]);

        $exam->update($data);

        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam) // FR-3.4
    {
        $this->authorizeOwnership($exam);
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    public function duplicate(Exam $exam) // FR-3.7
    {
        $this->authorizeOwnership($exam);

        $copy = $exam->replicate(['status']);
        $copy->status = 'draft';
        $copy->title = $exam->title.' (Copy)';
        $copy->save();

        foreach ($exam->questions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->exam_id = $copy->id;
            $newQuestion->save();

            foreach ($question->choices as $choice) {
                $newChoice = $choice->replicate();
                $newChoice->question_id = $newQuestion->id;
                $newChoice->save();
            }
        }

        return redirect()->route('exams.edit', $copy)->with('success', 'Exam duplicated.');
    }

    public function leaderboard(Exam $exam) // Sprint 6, FR-6.1/6.3
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Leaderboard', [
            'exam' => $exam,
            'entries' => $exam->leaderboardEntries()->with('student', 'score')->get(),
        ]);
    }

    public function analytics(Exam $exam) // Sprint 7, FR-7.1/7.2
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Analytics', [
            'exam' => $exam,
            // TODO Sprint 7: class average, high/low, per-question % correct.
        ]);
    }

    protected function authorizeOwnership(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->id(), 403);
    }

    protected function authorizeNotStarted(Exam $exam): void
    {
        abort_if($exam->hasStartedSubmissions(), 422, 'Cannot edit an exam that students have already started.'); // FR-3.6
    }
}
