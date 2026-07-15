<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Score;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

/**
 * Auto-grading engine (FR-5.1, FR-5.2).
 *
 * Kept as a single service class — deliberately NOT duplicated between the
 * Inertia (web) controllers and the API (mobile) controllers — so grading
 * behaves identically no matter which client triggered the submission.
 * See SRS 5.5 (Maintainability).
 */
class GradingService
{
    public function gradeSubmission(Submission $submission): Score
    {
        return DB::transaction(function () use ($submission) {
            $totalEarned = 0;
            $totalPossible = 0;

            foreach ($submission->answers()->with('question.choices')->get() as $answer) {
                $result = $this->gradeAnswer($answer);
                $answer->update([
                    'is_correct' => $result['is_correct'],
                    'points_earned' => $result['points_earned'],
                ]);

                $totalEarned += $result['points_earned'];
                $totalPossible += $answer->question->points;
            }

            $submission->update(['status' => 'graded']);

            return Score::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'total_points_earned' => $totalEarned,
                    'total_points_possible' => $totalPossible,
                    'percentage' => $totalPossible > 0 ? round(($totalEarned / $totalPossible) * 100, 2) : 0,
                    'graded_at' => now(),
                ]
            );
        });
    }

    /**
     * @return array{is_correct: bool|null, points_earned: float}
     */
    protected function gradeAnswer(Answer $answer): array
    {
        $question = $answer->question;

        return match ($question->type) {
            'mcq' => $this->gradeMcq($question, $answer),
            'true_false' => $this->gradeExactMatch($question, $answer),
            'identification' => $this->gradeExactMatch($question, $answer),
            'matching' => $this->gradeMatching($question, $answer),
            default => ['is_correct' => null, 'points_earned' => 0], // unknown/manual type
        };
    }

    protected function gradeMcq(Question $question, Answer $answer): array
    {
        $selectedChoiceId = $answer->response['choice_id'] ?? null;
        $correctChoiceId = $question->choices->firstWhere('is_correct', true)?->id;

        $isCorrect = $selectedChoiceId !== null && $selectedChoiceId == $correctChoiceId;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $question->points : 0,
        ];
    }

    /** Shared by true_false and identification — both compare a single value to answer_key. */
    protected function gradeExactMatch(Question $question, Answer $answer): array
    {
        $expected = $this->normalize($question->answer_key['answer'] ?? null);
        $given = $this->normalize($answer->response['answer'] ?? null);

        $isCorrect = $expected !== null && $expected === $given;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $question->points : 0,
        ];
    }

    protected function gradeMatching(Question $question, Answer $answer): array
    {
        // response: { "pairs": [{"choice_id": 1, "match_value": "..."}], ... }
        $submittedPairs = collect($answer->response['pairs'] ?? []);
        $choices = $question->choices;

        if ($choices->isEmpty() || $submittedPairs->count() !== $choices->count()) {
            return ['is_correct' => false, 'points_earned' => 0];
        }

        $correctPairs = 0;
        foreach ($submittedPairs as $pair) {
            $choice = $choices->firstWhere('id', $pair['choice_id'] ?? null);
            if ($choice && $this->normalize($choice->match_value) === $this->normalize($pair['match_value'] ?? null)) {
                $correctPairs++;
            }
        }

        // Partial credit proportional to correct pairs, matching the spirit
        // of FR-5.1 (objective, deterministic auto-grading).
        $fraction = $correctPairs / $choices->count();

        return [
            'is_correct' => $fraction === 1.0,
            'points_earned' => round($question->points * $fraction, 2),
        ];
    }

    protected function normalize(?string $value): ?string
    {
        return $value === null ? null : trim(mb_strtolower($value));
    }
}
