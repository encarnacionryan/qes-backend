<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Score;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class GradingService
{
    public function gradeSubmission(Submission $submission): Score
    {
        return DB::transaction(function () use ($submission) {
            $totalEarned = 0;
            $totalPossible = 0;

            $existingAnswers = $submission->answers()->with('question.choices')->get()
                ->keyBy('question_id');

            foreach ($submission->exam->questions()->with('choices')->get() as $question) {
                $answer = $existingAnswers->get($question->id);

                if (! $answer) {
                    $answer = $submission->answers()->create([
                        'question_id' => $question->id,
                        'response' => null,
                    ]);
                    $answer->setRelation('question', $question);
                }

                $result = $this->gradeAnswer($answer);
                $answer->update([
                    'is_correct' => $result['is_correct'],
                    'points_earned' => $result['points_earned'],
                ]);

                $totalEarned += $result['points_earned'];
                $totalPossible += $question->points;
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
            default => ['is_correct' => null, 'points_earned' => 0], 
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
        $choices = $question->choices;

        if ($choices->isEmpty()) {
            return ['is_correct' => false, 'points_earned' => 0];
        }

        $submittedPairs = collect($answer->response['pairs'] ?? [])->keyBy('choice_id');

        $correctPairs = 0;
        foreach ($choices as $choice) {
            $submitted = $submittedPairs->get($choice->id);
            $submittedValue = is_array($submitted) ? ($submitted['match_value'] ?? null) : null;

            if ($this->normalize($choice->match_value) === $this->normalize($submittedValue)) {
                $correctPairs++;
            }
        }

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
