<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = ExamSession::where('status', 'open')
            ->with(['exam:id,title,time_limit_minutes,total_points', 'teacher:id,name'])
            ->latest()
            ->get()
            ->map(fn (ExamSession $session) => [
                'id' => $session->id,
                'visibility' => $session->visibility,
                'requires_password' => $session->isPrivate(),
                'exam' => $session->exam,
                'teacher' => $session->teacher,
            ]);

        return response()->json($sessions);
    }

    public function join(Request $request, ExamSession $examSession)
    {
        abort_unless($examSession->isOpen(), 422, 'This exam session is no longer open.');

        $data = $request->validate([
            'password' => ['nullable', 'string'],
        ]);

        abort_unless(
            $examSession->checkPassword($data['password'] ?? null),
            403,
            'Incorrect password.'
        );

        $studentId = $request->user()->id;
        $exam = $examSession->exam;

        $attemptCount = $examSession->submissions()->where('student_id', $studentId)->count();
        abort_if($attemptCount > 0 && ! $exam->allow_retake, 422, 'You have already joined this session.');

        $submission = $examSession->submissions()->create([
            'exam_id' => $exam->id,
            'student_id' => $studentId,
            'started_at' => now(),
            'status' => 'in_progress',
            'attempt_number' => $attemptCount + 1,
        ]);

        return response()->json([
            'submission' => $submission,
            'exam' => $exam->load(['questions' => function ($q) {
                $q->select('id', 'exam_id', 'type', 'prompt', 'points', 'order')
                    ->with(['choices:id,question_id,label,order']); 
            }]),
        ]);
    }
    
    public function show(Request $request, ExamSession $examSession)
    {
        $hasJoined = $examSession->submissions()
            ->where('student_id', $request->user()->id)
            ->exists();

        abort_unless($hasJoined, 403, 'Join this session before viewing its content.');

        return response()->json(
            $examSession->exam->load(['questions' => fn ($q) => $q->with('choices:id,question_id,label,order')])
        );
    }
}
