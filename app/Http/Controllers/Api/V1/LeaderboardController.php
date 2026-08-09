<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function show(Request $request, Exam $exam) 
    {
        $entries = $exam->leaderboardEntries()->with('student:id,name')->get();

        if ($exam->anonymize_leaderboard) { 
            $entries->transform(function ($entry) {
                $entry->student->name = 'Student #'.$entry->student_id;

                return $entry;
            });
        }

        return response()->json($entries);
    }

    public function classAggregate(Request $request, SchoolClass $schoolClass) 
    {
        return response()->json(['message' => 'Not implemented yet — QES-42 (Could priority).'], 501);
    }
}
