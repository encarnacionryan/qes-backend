<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function show(Request $request, Exam $exam)
    {
        $hasCompleted = $exam->submissions()
            ->where('student_id', $request->user()->id)
            ->where('status', 'graded')
            ->exists();

        if (! $hasCompleted) {
            return redirect()->route('student.sessions.index')
                ->with('error', 'Complete this exam first to view its leaderboard.');
        }

        $entries = $exam->leaderboardEntries()->with('student:id,name', 'score')->get();

        if ($exam->anonymize_leaderboard) { 
            $entries->transform(function ($entry) use ($request) {
                if ($entry->student_id !== $request->user()->id) {
                    $entry->student->name = 'Student #'.$entry->student_id;
                }
                return $entry;
            });
        }

        return Inertia::render('Student/Exam/Leaderboard', [
            'exam' => $exam,
            'entries' => $entries,
            'myStudentId' => $request->user()->id,
        ]);
    }
}
