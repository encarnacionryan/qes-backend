<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

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

    public function store(Request $request) 
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

        return redirect()->route('exams.edit', $exam);
    }

    public function edit(Exam $exam)
    {
        $this->authorizeOwnership($exam);
  
        return Inertia::render('Exams/Edit', ['exam' => $exam->load('questions.choices')]);
    }

    public function update(Request $request, Exam $exam) 
    {
        $this->authorizeOwnership($exam);

        if ($blocked = $this->blockIfStarted($exam)) {
            return $blocked;
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['required', 'integer', 'min:1'],
            'show_score_immediately' => ['boolean'],
            'allow_retake' => ['boolean'],
            'anonymize_leaderboard' => ['boolean'],
            'shuffle_questions' => ['boolean'],
        ]);

        $exam->update($data);

        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeOwnership($exam);
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    public function duplicate(Exam $exam) 
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

    public function leaderboard(Exam $exam) 
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Leaderboard', [
            'exam' => $exam,
            'entries' => $exam->leaderboardEntries()->with('student', 'score')->get(),
        ]);
    }

    public function exportGradebook(Exam $exam)
    {
        $this->authorizeOwnership($exam);

        $entries = $exam->leaderboardEntries()->with('student', 'score')->get();
        $filename = Str::slug($exam->title).'-gradebook.csv';

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Rank', 'Name', 'Email', 'Points Earned', 'Points Possible', 'Percentage', 'Time Taken (seconds)']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->rank,
                    $entry->student->name,
                    $entry->student->email,
                    $entry->score->total_points_earned,
                    $entry->score->total_points_possible,
                    $entry->score->percentage,
                    $entry->completion_seconds,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function analytics(Exam $exam, \App\Services\AnalyticsService $analytics) 
    {
        $this->authorizeOwnership($exam);

        return Inertia::render('Exams/Analytics', [
            'exam' => $exam,
            'stats' => $analytics->forExam($exam),
        ]);
    }

    protected function authorizeOwnership(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->id(), 403);
    }

    protected function blockIfStarted(Exam $exam): ?\Illuminate\Http\RedirectResponse
    {
        if ($exam->hasStartedSubmissions()) {
            return back()->withErrors([
                'exam' => 'This exam can\'t be edited anymore — a student has already started it.',
            ]);
        }

        return null;
    }
}
