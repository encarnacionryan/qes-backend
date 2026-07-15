<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Sprint 4: QES-27, QES-28 (exam listing/detail for the mobile app).
 */
class ExamController extends Controller
{
    public function index(Request $request) // FR-4.1
    {
        $studentId = $request->user()->id;
        $classIds = $request->user()->enrollments()->pluck('school_class_id');

        $exams = Exam::where('status', 'published')
            ->whereHas('classes', function ($q) use ($classIds) {
                $q->whereIn('school_classes.id', $classIds)
                    ->wherePivot('opens_at', '<=', now())
                    ->wherePivot('closes_at', '>=', now());
            })
            ->with(['classes' => fn ($q) => $q->whereIn('school_classes.id', $classIds)])
            ->get();

        return response()->json($exams);
    }

    public function show(Request $request, Exam $exam) // FR-4.2 — never expose answer_key here
    {
        $classIds = $request->user()->enrollments()->pluck('school_class_id');
        abort_unless($exam->classes()->whereIn('school_classes.id', $classIds)->exists(), 403);

        return response()->json(
            $exam->load(['questions' => function ($q) {
                $q->select('id', 'exam_id', 'type', 'prompt', 'points', 'order')
                    ->with(['choices:id,question_id,label,order']); // is_correct/match_value withheld
            }])
        );
    }
}
