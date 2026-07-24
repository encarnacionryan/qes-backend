<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public function store(Request $request, Exam $exam)
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);

        $data = $this->validated($request);

        $question = $exam->questions()->create([
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'points' => $data['points'],
            'order' => $exam->questions()->max('order') + 1,
            'answer_key' => in_array($data['type'], ['true_false', 'identification'])
                ? ['answer' => $data['answer']]
                : null,
        ]);

        if ($data['type'] === 'mcq') {
            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'label' => $choice['label'],
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'order' => $i,
                ]);
            }
        }

        if ($data['type'] === 'matching') {
            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'label' => $choice['label'],
                    'match_value' => $choice['match_value'],
                    'order' => $i,
                ]);
            }
        }

        $this->recalculateTotalPoints($exam);

        return back()->with('success', 'Question added.');
    }

    public function update(Request $request, Exam $exam, Question $question)
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);
        abort_unless($question->exam_id === $exam->id, 404);

        $data = $this->validated($request);

        $question->update([
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'points' => $data['points'],
            'answer_key' => in_array($data['type'], ['true_false', 'identification'])
                ? ['answer' => $data['answer']]
                : null,
        ]);

        $question->choices()->delete();

        if ($data['type'] === 'mcq') {
            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'label' => $choice['label'],
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'order' => $i,
                ]);
            }
        }

        if ($data['type'] === 'matching') {
            foreach ($data['choices'] as $i => $choice) {
                $question->choices()->create([
                    'label' => $choice['label'],
                    'match_value' => $choice['match_value'],
                    'order' => $i,
                ]);
            }
        }

        $this->recalculateTotalPoints($exam);

        return back()->with('success', 'Question updated.');
    }

    public function destroy(Exam $exam, Question $question)
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);
        abort_unless($question->exam_id === $exam->id, 404);

        $question->delete();
        $this->recalculateTotalPoints($exam);

        return back()->with('success', 'Question removed.');
    }

    public function reorder(Request $request, Exam $exam)
    {
        $this->authorizeOwnership($exam);
        $this->authorizeNotStarted($exam);

        $data = $request->validate([
            'question_ids' => ['required', 'array'],
            'question_ids.*' => ['exists:questions,id'],
        ]);

        foreach ($data['question_ids'] as $i => $id) {
            Question::where('id', $id)->where('exam_id', $exam->id)->update(['order' => $i]);
        }

        return back();
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['mcq', 'true_false', 'identification', 'matching'])],
            'prompt' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            // true_false / identification only:
            'answer' => ['required_if:type,true_false,identification', 'nullable', 'string'],
            // mcq / matching only:
            'choices' => ['required_if:type,mcq,matching', 'nullable', 'array', 'min:2'],
            'choices.*.label' => ['required_with:choices', 'string'],
            'choices.*.is_correct' => ['nullable', 'boolean'], // mcq
            'choices.*.match_value' => ['required_if:type,matching', 'nullable', 'string'], // matching
        ]);
    }

    protected function recalculateTotalPoints(Exam $exam): void
    {
        $exam->update(['total_points' => $exam->questions()->sum('points')]);
    }

    protected function authorizeOwnership(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->id(), 403);
    }

    protected function authorizeNotStarted(Exam $exam): void
    {
        abort_if($exam->hasStartedSubmissions(), 422, 'Cannot edit questions after students have started this exam.'); // FR-3.6
    }
}
