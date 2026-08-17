<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Choice;
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

        $exam = $submission->exam()->with(['questions' => function ($q) {
            $q->select('id', 'exam_id', 'type', 'prompt', 'points', 'order')
                ->with(['choices:id,question_id,label,order']); 
        }])->first();

        if ($exam->shuffle_questions) {
            $this->shuffleForStudent($exam, $submission);
        }

        $this->attachMatchOptions($exam, $submission);

        return Inertia::render('Student/Exam/Take', [
            'submission' => $submission->load('answers'),
            'exam' => $exam,
        ]);
    }

    protected function attachMatchOptions($exam, Submission $submission): void
    {
        foreach ($exam->questions as $question) {
            if ($question->type !== 'matching') {
                continue;
            }

            $values = Choice::where('question_id', $question->id)->pluck('match_value');
            $seed = ($submission->id * 100000) + $question->id + 500000; 
            $question->setAttribute('match_options', $this->seededShuffle($values, $seed)->values());
        }
    }

    protected function shuffleForStudent($exam, Submission $submission): void
    {
        $questions = $this->seededShuffle($exam->questions, $submission->id);

        foreach ($questions as $question) {
            if (in_array($question->type, ['mcq', 'matching'], true)) {
                $seed = ($submission->id * 100000) + $question->id;
                $question->setRelation('choices', $this->seededShuffle($question->choices, $seed));
            }
        }

        $exam->setRelation('questions', $questions);
    }

    protected function seededShuffle($items, int $seed)
    {
        $array = $items->all();
        mt_srand($seed);
        shuffle($array);
        mt_srand();

        return collect($array)->values();
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

    public function submit(Submission $submission, GradingService $grading, LeaderboardService $leaderboard) 
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
