<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\GradingService;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function take(Submission $submission)
    {
        $this->authorizeOwnership($submission);

        return Inertia::render('Student/Exam/Take', [
            'submission' => $submission->load('answers'),
            'exam' => $submission->exam->load(['questions' => function ($q) {
                $q->select('id', 'exam_id', 'type', 'prompt', 'points', 'order')
                    ->with(['choices:id,question_id,label,order']); 
            }]),
        ]);
    }

    public function saveAnswers(Request $request, Submission $submission) 
    {
        $this->authorizeOwnership($submission);

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.response' => ['required'],
        ]);

        foreach ($data['answers'] as $a) {
            $submission->answers()->updateOrCreate(
                ['question_id' => $a['question_id']],
                ['response' => $a['response']]
            );
        }

        return response()->json(['message' => 'Saved.']);
    }

    public function submit(Submission $submission, GradingService $grading, LeaderboardService $leaderboard) // FR-4.6, FR-5.1
    {
        $this->authorizeOwnership($submission);

        if ($submission->status === 'in_progress') {
            $submission->update(['submitted_at' => now(), 'status' => 'submitted']);
            $grading->gradeSubmission($submission);
            $leaderboard->updateForSubmission($submission); 
        }

        return redirect()->route('student.submissions.score', $submission);
    }

    public function score(Submission $submission) 
    {
        $this->authorizeOwnership($submission);

        $released = $submission->exam->show_score_immediately
            || $submission->examSession->status === 'closed';

        if (! $released) {
            return redirect()->route('student.sessions.index')
                ->with('success', 'Exam submitted! Your score will be available once your teacher closes the session.');
        }

        return Inertia::render('Student/Exam/Score', [
            'submission' => $submission->load('score', 'answers'),
        ]);
    }

    protected function authorizeOwnership(Submission $submission): void
    {
        abort_unless($submission->student_id === auth()->id(), 403);
    }
}
