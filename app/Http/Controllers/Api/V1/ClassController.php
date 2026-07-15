<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

/**
 * Sprint 2: QES-16 (join class from mobile).
 */
class ClassController extends Controller
{
    public function join(Request $request) // FR-2.3
    {
        $data = $request->validate([
            'join_code' => ['required', 'string'],
        ]);

        $class = SchoolClass::where('join_code', strtoupper($data['join_code']))
            ->where('is_archived', false)
            ->first();

        abort_if(! $class, 404, 'Invalid join code.');

        $alreadyEnrolled = $class->enrollments()->where('student_id', $request->user()->id)->exists();
        abort_if($alreadyEnrolled, 422, 'You are already enrolled in this class.'); // FR-2.6

        $class->enrollments()->create(['student_id' => $request->user()->id]);

        return response()->json(['class' => $class]);
    }

    public function mine(Request $request)
    {
        return response()->json(
            $request->user()->enrollments()->with('schoolClass')->get()->pluck('schoolClass')
        );
    }
}
