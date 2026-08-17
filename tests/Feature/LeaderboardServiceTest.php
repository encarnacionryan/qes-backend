<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Services\GradingService;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaderboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeExamWithOneQuestion(): Exam
    {
        $teacher = User::create([
            'name' => 'Teacher', 'email' => 'teacher@test.local',
            'password' => bcrypt('password'), 'role' => 'teacher', 'is_lead_teacher' => true,
        ]);

        $exam = Exam::create([
            'teacher_id' => $teacher->id, 'title' => 'Exam', 'time_limit_minutes' => 30,
            'total_points' => 10, 'status' => 'published',
        ]);

        Question::create([
            'exam_id' => $exam->id, 'type' => 'identification', 'prompt' => 'Q',
            'points' => 10, 'order' => 1, 'answer_key' => ['answer' => 'correct'],
        ]);

        return $exam;
    }

    protected function submitAndGrade(Exam $exam, string $studentName, string $answerText, int $startOffsetSeconds, int $durationSeconds): Submission
    {
        $student = User::create([
            'name' => $studentName, 'email' => strtolower($studentName).'@test.local',
            'password' => bcrypt('password'), 'role' => 'student',
        ]);

        $session = ExamSession::firstOrCreate(
            ['exam_id' => $exam->id],
            ['teacher_id' => $exam->teacher_id, 'visibility' => 'public', 'status' => 'open']
        );

        $started = now()->addSeconds($startOffsetSeconds);

        $submission = Submission::create([
            'exam_id' => $exam->id, 'exam_session_id' => $session->id, 'student_id' => $student->id,
            'started_at' => $started, 'submitted_at' => $started->copy()->addSeconds($durationSeconds),
            'status' => 'submitted', 'attempt_number' => 1,
        ]);

        Answer::create([
            'submission_id' => $submission->id,
            'question_id' => $exam->questions()->first()->id,
            'response' => ['answer' => $answerText],
        ]);

        app(GradingService::class)->gradeSubmission($submission);
        app(LeaderboardService::class)->updateForSubmission($submission);

        return $submission;
    }

    #[Test]
    public function it_ranks_higher_scores_above_lower_scores(): void
    {
        $exam = $this->makeExamWithOneQuestion();

        $this->submitAndGrade($exam, 'Alice', 'wrong', 0, 60);   
        $this->submitAndGrade($exam, 'Bob', 'correct', 0, 90);   

        $entries = $exam->fresh()->leaderboardEntries()->with('student')->get();

        $this->assertEquals('Bob', $entries[0]->student->name);
        $this->assertEquals(1, $entries[0]->rank);
        $this->assertEquals('Alice', $entries[1]->student->name);
        $this->assertEquals(2, $entries[1]->rank);
    }

    #[Test]
    public function it_uses_completion_time_as_a_tiebreaker_when_scores_are_equal(): void
    {
        $exam = $this->makeExamWithOneQuestion();
        $this->submitAndGrade($exam, 'Dave', 'correct', 0, 120);
        $this->submitAndGrade($exam, 'Carol', 'correct', 0, 60);

        $entries = $exam->fresh()->leaderboardEntries()->with('student')->get();

        $this->assertEquals('Carol', $entries[0]->student->name);
        $this->assertEquals(60, $entries[0]->completion_seconds);
        $this->assertEquals('Dave', $entries[1]->student->name);
    }

    #[Test]
    public function a_retake_updates_the_existing_entry_instead_of_adding_a_second_row(): void
    {
        $exam = $this->makeExamWithOneQuestion();
        $exam->update(['allow_retake' => true]);

        $student = User::create([
            'name' => 'Eve', 'email' => 'eve@test.local',
            'password' => bcrypt('password'), 'role' => 'student',
        ]);
        $session = ExamSession::create([
            'exam_id' => $exam->id, 'teacher_id' => $exam->teacher_id,
            'visibility' => 'public', 'status' => 'open',
        ]);

        $first = Submission::create([
            'exam_id' => $exam->id, 'exam_session_id' => $session->id, 'student_id' => $student->id,
            'started_at' => now(), 'submitted_at' => now()->addSeconds(60),
            'status' => 'submitted', 'attempt_number' => 1,
        ]);
        Answer::create([
            'submission_id' => $first->id, 'question_id' => $exam->questions()->first()->id,
            'response' => ['answer' => 'wrong'],
        ]);
        app(GradingService::class)->gradeSubmission($first);
        app(LeaderboardService::class)->updateForSubmission($first);

        $second = Submission::create([
            'exam_id' => $exam->id, 'exam_session_id' => $session->id, 'student_id' => $student->id,
            'started_at' => now(), 'submitted_at' => now()->addSeconds(45),
            'status' => 'submitted', 'attempt_number' => 2,
        ]);
        Answer::create([
            'submission_id' => $second->id, 'question_id' => $exam->questions()->first()->id,
            'response' => ['answer' => 'correct'],
        ]);
        app(GradingService::class)->gradeSubmission($second);
        app(LeaderboardService::class)->updateForSubmission($second);

        $entries = $exam->fresh()->leaderboardEntries()->get();

        $this->assertCount(1, $entries);
        $this->assertEquals($second->score()->first()->id, $entries->first()->score_id);
    }
}
