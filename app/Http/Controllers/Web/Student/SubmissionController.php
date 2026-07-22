<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * PWA update: same exam-taking flow previously built for the native app's
 * API (Api\V1\SubmissionController), now also available as an
 * Inertia-rendered page so the PWA itself can host the exam UI directly.
 * The service worker (public/sw.js) is what keeps this usable through
 * brief connectivity drops on the local hotspot — see FR-4.5/4.6.
 */
class SubmissionController extends Controller
{
    public function take(Submission $submission)
    {
        $this->authorizeOwnership($submission);

        return Inertia::render('Student/Exam/Take', [
            'submission' => $submission,
            'exam' => $submission->exam->load(['questions' => function ($q) {
                $q->select('id', 'exam_id', 'type', 'prompt', 'points', 'order')
                    ->with(['choices:id,question_id,label,order']); // answer key withheld
            }]),
        ]);
    }

    public function saveAnswers(Request $request, Submission $submission) // FR-4.5
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

    public function submit(Submission $submission, GradingService $grading) // FR-4.6, FR-5.1
    {
        $this->authorizeOwnership($submission);

        if ($submission->status === 'in_progress') {
            $submission->update(['submitted_at' => now(), 'status' => 'submitted']);
            $grading->gradeSubmission($submission);
        }

        return redirect()->route('student.submissions.score', $submission);
    }

    public function score(Submission $submission) // FR-5.3
    {
        $this->authorizeOwnership($submission);

        $released = $submission->exam->show_score_immediately
            || $submission->examSession->status === 'closed';

        abort_unless($released, 403, 'Scores are released once the session closes.');

        return Inertia::render('Student/Exam/Score', [
            'submission' => $submission->load('score', 'answers'),
        ]);
    }

    protected function authorizeOwnership(Submission $submission): void
    {
        abort_unless($submission->student_id === auth()->id(), 403);
    }
}
