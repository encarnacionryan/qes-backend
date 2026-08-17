<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\LeaderboardEntry;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function updateForSubmission(Submission $submission): void
    {
        DB::transaction(function () use ($submission) {
            $score = $submission->score;
            if (! $score) {
                return; 
            }

            LeaderboardEntry::updateOrCreate(
                ['exam_id' => $submission->exam_id, 'student_id' => $submission->student_id],
                [
                    'score_id' => $score->id,
                    'completion_seconds' => $submission->completionSeconds() ?? 0,
                    'rank' => 0,
                ]
            );

            $this->recomputeRanks($submission->exam);
        });
    }

    protected function recomputeRanks(Exam $exam): void
    {
        $ranked = LeaderboardEntry::where('exam_id', $exam->id)
            ->join('scores', 'scores.id', '=', 'leaderboard_entries.score_id')
            ->orderByDesc('scores.total_points_earned')
            ->orderBy('leaderboard_entries.completion_seconds')
            ->pluck('leaderboard_entries.id');

        foreach ($ranked->values() as $i => $entryId) {
            LeaderboardEntry::where('id', $entryId)->update(['rank' => $i + 1]);
        }
    }
}
