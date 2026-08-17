<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
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

    public function submit(Request $request, Submission $submission, GradingService $grading) 
    {
        $this->authorizeOwnership($submission);

        if ($submission->status === 'in_progress') {
            $submission->update(['submitted_at' => now(), 'status' => 'submitted']);
            $grading->gradeSubmission($submission); 
        }
\
        return response()->json($submission->fresh('score'));
    }

    public function score(Request $request, Submission $submission) 
    {
        $this->authorizeOwnership($submission);

        $exam = $submission->exam;
        $released = $exam->show_score_immediately || $submission->examSession->status === 'closed';

        abort_unless($released, 403, 'Scores are released once the session closes.');

        return response()->json($submission->load('score', 'answers'));
    }

    protected function authorizeOwnership(Submission $submission): void
    {
        abort_unless($submission->student_id === auth()->id(), 403);
    }
}
