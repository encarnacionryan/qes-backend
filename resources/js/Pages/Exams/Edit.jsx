
import { useState } from "react";
import { Head, Link, useForm, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

const QUESTION_TYPES = [
    { value: "mcq", label: "Multiple Choice" },
    { value: "true_false", label: "True / False" },
    { value: "identification", label: "Identification" },
    { value: "matching", label: "Matching" },
];

const emptyChoice = (type) => (type === "matching" ? { label: "", match_value: "" } : { label: "", is_correct: false });

function blankForm() {
    return {
        type: "mcq",
        prompt: "",
        points: 1,
        answer: "true",
        choices: [emptyChoice("mcq"), emptyChoice("mcq")],
    };
}

export default function Edit({ exam }) {
    const [editingId, setEditingId] = useState(null); // null = adding new
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, put, processing, errors, reset } = useForm(blankForm());

    const questions = exam.questions || [];

    function startAdd() {
        reset();
        setData(blankForm());
        setEditingId(null);
        setShowForm(true);
    }

    function startEdit(question) {
        setData({
            type: question.type,
            prompt: question.prompt,
            points: question.points,
            answer: question.answer_key?.answer ?? "true",
            choices:
                question.choices?.length > 0
                    ? question.choices.map((c) => ({
                          label: c.label,
                          is_correct: !!c.is_correct,
                          match_value: c.match_value || "",
                      }))
                    : [emptyChoice(question.type), emptyChoice(question.type)],
        });
        setEditingId(question.id);
        setShowForm(true);
    }

    function changeType(type) {
        setData((prev) => ({
            ...prev,
            type,
            choices: [emptyChoice(type), emptyChoice(type)],
        }));
    }

    function updateChoice(index, field, value) {
        const next = [...data.choices];
        next[index] = { ...next[index], [field]: value };
        // For MCQ, enforce single-correct-answer by clearing others when one is checked.
        if (field === "is_correct" && value === true && data.type === "mcq") {
            next.forEach((c, i) => {
                if (i !== index) c.is_correct = false;
            });
        }
        setData("choices", next);
    }

    function addChoice() {
        setData("choices", [...data.choices, emptyChoice(data.type)]);
    }

    function removeChoice(index) {
        setData("choices", data.choices.filter((_, i) => i !== index));
    }

    function handleSubmit(e) {
        e.preventDefault();
        const onSuccess = () => {
            reset();
            setShowForm(false);
            setEditingId(null);
        };

        if (editingId) {
            put(`/exams/${exam.id}/questions/${editingId}`, { onSuccess });
        } else {
            post(`/exams/${exam.id}/questions`, { onSuccess });
        }
    }

    function deleteQuestion(question) {
        if (!confirm("Delete this question?")) return;
        router.delete(`/exams/${exam.id}/questions/${question.id}`);
    }

    const needsChoices = data.type === "mcq" || data.type === "matching";

    return (
        <AuthenticatedLayout>
            <Head title={`Edit ${exam.title}`} />

            <div className="flex items-start justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-[#1F3864]">{exam.title}</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {exam.time_limit_minutes} min · {exam.total_points} total points ·{" "}
                        {questions.length} question{questions.length === 1 ? "" : "s"}
                    </p>
                </div>
                <Link
                    href={`/exams/${exam.id}/sessions`}
                    className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    Host a Session →
                </Link>
            </div>

            {/* Existing questions */}
            <div className="space-y-3 mb-6">
                {questions.map((q, i) => (
                    <div key={q.id} className="bg-white rounded-xl shadow p-4 flex items-start justify-between">
                        <div>
                            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">
                                Q{i + 1} · {QUESTION_TYPES.find((t) => t.value === q.type)?.label} · {q.points} pt
                                {q.points === 1 ? "" : "s"}
                            </p>
                            <p className="text-gray-800">{q.prompt}</p>
                            {q.choices?.length > 0 && (
                                <ul className="mt-2 text-sm text-gray-500 space-y-0.5">
                                    {q.choices.map((c) => (
                                        <li key={c.id}>
                                            {q.type === "mcq" ? (c.is_correct ? "✓ " : "· ") : "· "}
                                            {c.label}
                                            {q.type === "matching" && c.match_value ? ` → ${c.match_value}` : ""}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                        <div className="flex gap-3 text-sm shrink-0 ml-4">
                            <button onClick={() => startEdit(q)} className="text-[#1F3864] underline">
                                Edit
                            </button>
                            <button onClick={() => deleteQuestion(q)} className="text-red-600 underline">
                                Delete
                            </button>
                        </div>
                    </div>
                ))}

                {questions.length === 0 && !showForm && (
                    <p className="text-sm text-gray-400">No questions yet — add your first one below.</p>
                )}
            </div>

            {/* Add/edit form */}
            {showForm ? (
                <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-6 space-y-4">
                    <h2 className="font-semibold text-gray-700">
                        {editingId ? "Edit Question" : "Add Question"}
                    </h2>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select
                                value={data.type}
                                onChange={(e) => changeType(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2"
                            >
                                {QUESTION_TYPES.map((t) => (
                                    <option key={t.value} value={t.value}>
                                        {t.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Points</label>
                            <input
                                type="number"
                                min={1}
                                value={data.points}
                                onChange={(e) => setData("points", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Question</label>
                        <textarea
                            value={data.prompt}
                            onChange={(e) => setData("prompt", e.target.value)}
                            rows={2}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2"
                        />
                        {errors.prompt && <p className="text-red-600 text-sm mt-1">{errors.prompt}</p>}
                    </div>

                    {data.type === "true_false" && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Correct Answer</label>
                            <select
                                value={data.answer}
                                onChange={(e) => setData("answer", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2"
                            >
                                <option value="true">True</option>
                                <option value="false">False</option>
                            </select>
                        </div>
                    )}

                    {data.type === "identification" && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Correct Answer (exact match, not case-sensitive)
                            </label>
                            <input
                                type="text"
                                value={data.answer}
                                onChange={(e) => setData("answer", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2"
                            />
                            {errors.answer && <p className="text-red-600 text-sm mt-1">{errors.answer}</p>}
                        </div>
                    )}

                    {needsChoices && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                {data.type === "mcq" ? "Choices (check the correct one)" : "Matching Pairs"}
                            </label>
                            <div className="space-y-2">
                                {data.choices.map((choice, i) => (
                                    <div key={i} className="flex items-center gap-2">
                                        {data.type === "mcq" && (
                                            <input
                                                type="checkbox"
                                                checked={!!choice.is_correct}
                                                onChange={(e) => updateChoice(i, "is_correct", e.target.checked)}
                                            />
                                        )}
                                        <input
                                            type="text"
                                            placeholder={data.type === "matching" ? "Left side" : `Choice ${i + 1}`}
                                            value={choice.label}
                                            onChange={(e) => updateChoice(i, "label", e.target.value)}
                                            className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                        />
                                        {data.type === "matching" && (
                                            <input
                                                type="text"
                                                placeholder="Right side (match)"
                                                value={choice.match_value}
                                                onChange={(e) => updateChoice(i, "match_value", e.target.value)}
                                                className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                            />
                                        )}
                                        {data.choices.length > 2 && (
                                            <button
                                                type="button"
                                                onClick={() => removeChoice(i)}
                                                className="text-red-600 text-sm"
                                            >
                                                ✕
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                            <button
                                type="button"
                                onClick={addChoice}
                                className="mt-2 text-sm text-[#1F3864] underline"
                            >
                                + Add {data.type === "matching" ? "pair" : "choice"}
                            </button>
                            {errors.choices && <p className="text-red-600 text-sm mt-1">{errors.choices}</p>}
                        </div>
                    )}

                    <div className="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1F3864] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Saving…" : editingId ? "Save Question" : "Add Question"}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                setShowForm(false);
                                setEditingId(null);
                                reset();
                            }}
                            className="text-sm text-gray-500 underline"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            ) : (
                <button
                    onClick={startAdd}
                    className="w-full border-2 border-dashed border-gray-300 rounded-xl py-4 text-gray-500 hover:border-[#1F3864] hover:text-[#1F3864]"
                >
                    + Add a Question
                </button>
            )}
        </AuthenticatedLayout>
    );
}
