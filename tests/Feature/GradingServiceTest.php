<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Choice;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTeacherAndStudent(): array
    {
        $teacher = User::create([
            'name' => 'Test Teacher',
            'email' => 'teacher@test.local',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_lead_teacher' => true,
        ]);

        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student@test.local',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        return [$teacher, $student];
    }

    protected function makeExam(User $teacher): Exam
    {
        return Exam::create([
            'teacher_id' => $teacher->id,
            'title' => 'Test Exam',
            'time_limit_minutes' => 30,
            'total_points' => 0,
            'status' => 'published',
        ]);
    }

    protected function makeSubmission(Exam $exam, User $student): Submission
    {
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'teacher_id' => $exam->teacher_id,
            'visibility' => 'public',
            'status' => 'open',
        ]);

        return Submission::create([
            'exam_id' => $exam->id,
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'submitted_at' => now(),
            'status' => 'submitted',
            'attempt_number' => 1,
        ]);
    }

    
    public function it_grades_a_correct_mcq_answer(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $question = Question::create([
            'exam_id' => $exam->id, 'type' => 'mcq', 'prompt' => 'Capital of France?',
            'points' => 5, 'order' => 1,
        ]);
        $wrong = Choice::create(['question_id' => $question->id, 'label' => 'Berlin', 'is_correct' => false, 'order' => 1]);
        $right = Choice::create(['question_id' => $question->id, 'label' => 'Paris', 'is_correct' => true, 'order' => 2]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $question->id,
            'response' => ['choice_id' => $right->id],
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(5, $score->total_points_earned);
        $this->assertEquals(5, $score->total_points_possible);
        $this->assertEquals(100, $score->percentage);
        $this->assertTrue($submission->answers()->first()->is_correct);
    }

    
    public function it_marks_an_incorrect_mcq_answer_wrong_with_zero_points(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $question = Question::create([
            'exam_id' => $exam->id, 'type' => 'mcq', 'prompt' => 'Capital of France?',
            'points' => 5, 'order' => 1,
        ]);
        $wrong = Choice::create(['question_id' => $question->id, 'label' => 'Berlin', 'is_correct' => false, 'order' => 1]);
        Choice::create(['question_id' => $question->id, 'label' => 'Paris', 'is_correct' => true, 'order' => 2]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $question->id,
            'response' => ['choice_id' => $wrong->id],
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(0, $score->total_points_earned);
        $this->assertEquals(5, $score->total_points_possible);
        $this->assertFalse($submission->answers()->first()->is_correct);
    }

    
    public function it_grades_true_false_case_insensitively(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $question = Question::create([
            'exam_id' => $exam->id, 'type' => 'true_false', 'prompt' => 'The sky is blue.',
            'points' => 2, 'order' => 1, 'answer_key' => ['answer' => 'True'],
        ]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $question->id,
            'response' => ['answer' => 'true'], // different casing than the stored key
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(2, $score->total_points_earned);
        $this->assertTrue($submission->answers()->first()->is_correct);
    }

    
    public function it_grades_identification_with_trimmed_case_insensitive_match(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $question = Question::create([
            'exam_id' => $exam->id, 'type' => 'identification', 'prompt' => 'Powerhouse of the cell?',
            'points' => 3, 'order' => 1, 'answer_key' => ['answer' => 'Mitochondria'],
        ]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $question->id,
            'response' => ['answer' => '  mitochondria  '], // stray whitespace + different case
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(3, $score->total_points_earned);
        $this->assertTrue($submission->answers()->first()->is_correct);
    }

    
    public function it_awards_partial_credit_for_matching_questions(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $question = Question::create([
            'exam_id' => $exam->id, 'type' => 'matching', 'prompt' => 'Match capitals to countries.',
            'points' => 9, 'order' => 1,
        ]);
        $c1 = Choice::create(['question_id' => $question->id, 'label' => 'Paris', 'match_value' => 'France', 'order' => 1]);
        $c2 = Choice::create(['question_id' => $question->id, 'label' => 'Berlin', 'match_value' => 'Germany', 'order' => 2]);
        $c3 = Choice::create(['question_id' => $question->id, 'label' => 'Madrid', 'match_value' => 'Spain', 'order' => 3]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $question->id,
            'response' => ['pairs' => [
                ['choice_id' => $c1->id, 'match_value' => 'France'],   // correct
                ['choice_id' => $c2->id, 'match_value' => 'Germany'],  // correct
                ['choice_id' => $c3->id, 'match_value' => 'Italy'],    // wrong
            ]],
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(6, $score->total_points_earned);
        $this->assertFalse($submission->answers()->first()->is_correct); 
    }

    public function a_skipped_question_still_counts_toward_total_points_possible(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher);

        $answered = Question::create([
            'exam_id' => $exam->id, 'type' => 'identification', 'prompt' => 'Answered question',
            'points' => 5, 'order' => 1, 'answer_key' => ['answer' => 'foo'],
        ]);
        $skipped = Question::create([
            'exam_id' => $exam->id, 'type' => 'identification', 'prompt' => 'Skipped question',
            'points' => 5, 'order' => 2, 'answer_key' => ['answer' => 'bar'],
        ]);

        $submission = $this->makeSubmission($exam, $student);
        Answer::create([
            'submission_id' => $submission->id, 'question_id' => $answered->id,
            'response' => ['answer' => 'foo'],
        ]);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(10, $score->total_points_possible);
        $this->assertEquals(5, $score->total_points_earned);
        $this->assertEquals(50, $score->percentage);

        $skippedAnswer = $submission->answers()->where('question_id', $skipped->id)->first();
        $this->assertNotNull($skippedAnswer, 'GradingService should create an Answer row for skipped questions too.');
        $this->assertFalse($skippedAnswer->is_correct);
        $this->assertEquals(0, $skippedAnswer->points_earned);
    }

    public function percentage_is_zero_not_a_division_error_when_exam_has_no_points(): void
    {
        [$teacher, $student] = $this->makeTeacherAndStudent();
        $exam = $this->makeExam($teacher); 

        $submission = $this->makeSubmission($exam, $student);

        $score = app(GradingService::class)->gradeSubmission($submission);

        $this->assertEquals(0, $score->total_points_possible);
        $this->assertEquals(0, $score->total_points_earned);
        $this->assertEquals(0, $score->percentage);
    }
}
