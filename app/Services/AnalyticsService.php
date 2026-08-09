<?php

namespace App\Services;

use App\Models\Exam;

class AnalyticsService
{
    protected const LOW_PERFORMANCE_THRESHOLD = 50.0;

    public function forExam(Exam $exam): array
    {
        $scores = $exam->submissions()
            ->where('status', 'graded')
            ->with('score')
            ->get()
            ->pluck('score')
            ->filter();

        return [
            'submission_count' => $scores->count(),
            'average_percentage' => $scores->isEmpty() ? null : round($scores->avg('percentage'), 1),
            'high_percentage' => $scores->isEmpty() ? null : (float) $scores->max('percentage'),
            'low_percentage' => $scores->isEmpty() ? null : (float) $scores->min('percentage'),
            'distribution' => $this->buildDistribution($scores),
            'questions' => $this->buildQuestionBreakdown($exam),
        ];
    }

    protected function buildDistribution($scores): array
    {
        $buckets = [];
        for ($i = 0; $i < 10; $i++) {
            $label = $i === 9 ? '90-100%' : ($i * 10).'-'.($i * 10 + 9).'%';
            $buckets[$label] = 0;
        }

        foreach ($scores as $score) {
            $pct = (float) $score->percentage;
            $index = $pct >= 100 ? 9 : (int) floor($pct / 10);
            $label = $index === 9 ? '90-100%' : ($index * 10).'-'.($index * 10 + 9).'%';
            $buckets[$label]++;
        }

        return collect($buckets)->map(fn ($count, $label) => ['label' => $label, 'count' => $count])->values()->all();
    }

    protected function buildQuestionBreakdown(Exam $exam): array
    {
        $questions = $exam->questions()
            ->orderBy('order')
            ->with(['answers' => function ($q) {
                $q->whereHas('submission', fn ($s) => $s->where('status', 'graded'));
            }])
            ->get();

        return $questions->map(function ($question) {
            $answers = $question->answers;
            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $percentCorrect = $total > 0 ? round(($correct / $total) * 100, 1) : null;

            return [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'type' => $question->type,
                'points' => $question->points,
                'total_answered' => $total,
                'correct_count' => $correct,
                'percent_correct' => $percentCorrect,
                'flagged' => $percentCorrect !== null && $percentCorrect < self::LOW_PERFORMANCE_THRESHOLD, // FR-7.3
            ];
        })->all();
    }
}
