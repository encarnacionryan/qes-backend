<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

/**
 * Sprint 6: QES-39 (per-exam), QES-42 (Could — cross-exam class aggregate).
 */
class LeaderboardController extends Controller
{
    public function show(Request $request, Exam $exam) // FR-6.1, FR-6.2
    {
        $entries = $exam->leaderboardEntries()->with('student:id,name')->get();

        if ($exam->anonymize_leaderboard) { // FR-6.4
            $entries->transform(function ($entry) {
                $entry->student->name = 'Student #'.$entry->student_id;

                return $entry;
            });
        }

        return response()->json($entries);
    }

    public function classAggregate(Request $request, SchoolClass $schoolClass) // FR-6.5, Could
    {
        // TODO Sprint 6 (stretch): aggregate a student's scores across all
        // exams in this class into a single combined ranking.
        return response()->json(['message' => 'Not implemented yet — QES-42 (Could priority).'], 501);
    }
}
