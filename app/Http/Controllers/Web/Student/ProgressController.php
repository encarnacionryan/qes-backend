<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $submissions = $request->user()->submissions()
            ->where('status', 'graded')
            ->with(['exam:id,title', 'score'])
            ->get()
            ->sortBy('submitted_at')
            ->values();

        $history = $submissions->map(fn ($submission) => [
            'exam_title' => $submission->exam->title,
            'submitted_at' => $submission->submitted_at,
            'percentage' => (float) $submission->score->percentage,
            'points_earned' => (float) $submission->score->total_points_earned,
            'points_possible' => $submission->score->total_points_possible,
        ]);

        $percentages = $history->pluck('percentage');

        return Inertia::render('Student/Progress', [
            'history' => $history,
            'stats' => [
                'exams_taken' => $history->count(),
                'average_percentage' => $percentages->isEmpty() ? null : round($percentages->avg(), 1),
                'best_percentage' => $percentages->isEmpty() ? null : $percentages->max(),
                'trend' => $this->computeTrend($percentages),
            ],
        ]);
    }

    protected function computeTrend($percentages): ?string
    {
        if ($percentages->count() < 2) {
            return null;
        }

        $recent = $percentages->slice(-3);
        $earlier = $percentages->slice(0, max(0, $percentages->count() - 3));

        if ($earlier->isEmpty()) {
            return null;
        }

        $diff = $recent->avg() - $earlier->avg();

        if ($diff > 3) {
            return 'up';
        }
        if ($diff < -3) {
            return 'down';
        }

        return 'steady';
    }
}
