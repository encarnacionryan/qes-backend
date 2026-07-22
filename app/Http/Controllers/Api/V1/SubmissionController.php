<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Http\Request;

/**
 * Sprint 4 (QES-27 to QES-33) + Sprint 5 (QES-34 to QES-37).
 * saveAnswers() is called repeatedly as autosave while the student works
 * (FR-4.5); submit() is the final, retried-until-confirmed call (FR-4.6)
 * that triggers GradingService.
 */
class SubmissionController extends Controller
{
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

    public function submit(Request $request, Submission $submission, GradingService $grading) // FR-4.6, FR-5.1
    {
        $this->authorizeOwnership($submission);

        if ($submission->status === 'in_progress') {
            $submission->update(['submitted_at' => now(), 'status' => 'submitted']);
            $grading->gradeSubmission($submission); // FR-5.1, FR-5.2
        }

        // Idempotent: if the client retries this call after a network drop
        // (FR-4.6), we don't double-grade — just return the existing result.
        return response()->json($submission->fresh('score'));
    }

    public function score(Request $request, Submission $submission) // FR-5.3
    {
        $this->authorizeOwnership($submission);

        $exam = $submission->exam;
        // Score is released immediately if the exam allows it, or once the
        // session the student took it in has been closed by the teacher.
        $released = $exam->show_score_immediately || $submission->examSession->status === 'closed';

        abort_unless($released, 403, 'Scores are released once the session closes.');

        return response()->json($submission->load('score', 'answers'));
    }

    protected function authorizeOwnership(Submission $submission): void
    {
        abort_unless($submission->student_id === auth()->id(), 403);
    }
}
