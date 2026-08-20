import { useState } from "react";
import { Head, Link, useForm, usePage, router } from "@inertiajs/react";
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
    const { data, setData, post, put, processing, errors, reset, transform } = useForm(blankForm());
    const { errors: pageErrors } = usePage().props;

    const settingsForm = useForm({
        title: exam.title,
        description: exam.description || "",
        time_limit_minutes: exam.time_limit_minutes,
        show_score_immediately: exam.show_score_immediately,
        allow_retake: exam.allow_retake,
        anonymize_leaderboard: exam.anonymize_leaderboard,
        shuffle_questions: exam.shuffle_questions,
    });

    function saveSettings(e) {
        e.preventDefault();
        settingsForm.put(`/exams/${exam.id}`);
    }

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

        transform((formData) => {
            const payload = {
                type: formData.type,
                prompt: formData.prompt,
                points: formData.points,
            };
            if (formData.type === "true_false" || formData.type === "identification") {
                payload.answer = formData.answer;
            }
            if (formData.type === "mcq" || formData.type === "matching") {
                payload.choices = formData.choices;
            }
            return payload;
        });

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
                    <h1 className="font-display text-3xl font-semibold text-[#1B3A34]">{exam.title}</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {exam.time_limit_minutes} min · {exam.total_points} total points ·{" "}
                        {questions.length} question{questions.length === 1 ? "" : "s"}
                    </p>
                </div>
                <div className="flex gap-2">
                    <Link
                        href={`/exams/${exam.id}/analytics`}
                        className="bg-white border border-[#1B3A34] text-[#1B3A34] px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Analytics
                    </Link>
                    <Link
                        href={`/exams/${exam.id}/leaderboard`}
                        className="bg-white border border-[#1B3A34] text-[#1B3A34] px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Leaderboard
                    </Link>
                    <Link
                        href={`/exams/${exam.id}/sessions`}
                        className="bg-[#1B3A34] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Host a Session →
                    </Link>
                </div>
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
                            <button onClick={() => startEdit(q)} className="text-[#1B3A34] underline">
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
            {pageErrors?.exam && (
                <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-6">
                    {pageErrors.exam}
                </div>
            )}

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
                                className="mt-2 text-sm text-[#1B3A34] underline"
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
                            className="bg-[#1B3A34] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
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
                    className="w-full border-2 border-dashed border-gray-300 rounded-xl py-4 text-gray-500 hover:border-[#1B3A34] hover:text-[#1B3A34]"
                >
                    + Add a Question
                </button>
            )}

            <form onSubmit={saveSettings} className="mt-10 bg-white rounded-xl shadow p-6">
                <h2 className="font-semibold text-gray-700 mb-4">Exam Settings</h2>
                <div className="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            type="text"
                            value={settingsForm.data.title}
                            onChange={(e) => settingsForm.setData("title", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                        />
                        {settingsForm.errors.title && (
                            <p className="text-red-600 text-sm mt-1">{settingsForm.errors.title}</p>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Time Limit (minutes)
                        </label>
                        <input
                            type="number"
                            min={1}
                            value={settingsForm.data.time_limit_minutes}
                            onChange={(e) => settingsForm.setData("time_limit_minutes", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div className="space-y-3 mb-4">
                    <label className="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            checked={settingsForm.data.shuffle_questions}
                            onChange={(e) => settingsForm.setData("shuffle_questions", e.target.checked)}
                        />
                        Shuffle question order per student (choice order shuffles too for MCQ/Matching)
                    </label>
                    <label className="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            checked={settingsForm.data.show_score_immediately}
                            onChange={(e) => settingsForm.setData("show_score_immediately", e.target.checked)}
                        />
                        Show score immediately after submission (otherwise, released when the session closes)
                    </label>
                    <label className="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            checked={settingsForm.data.allow_retake}
                            onChange={(e) => settingsForm.setData("allow_retake", e.target.checked)}
                        />
                        Allow students to retake this exam
                    </label>
                    <label className="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            checked={settingsForm.data.anonymize_leaderboard}
                            onChange={(e) => settingsForm.setData("anonymize_leaderboard", e.target.checked)}
                        />
                        Anonymize student names on the leaderboard
                    </label>
                </div>

                <button
                    type="submit"
                    disabled={settingsForm.processing}
                    className="bg-[#1B3A34] text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                >
                    {settingsForm.processing ? "Saving…" : "Save Settings"}
                </button>
            </form>

            <div className="mt-10 bg-red-50 border border-red-100 rounded-xl p-5">
                <h2 className="text-sm font-semibold text-red-700 mb-1">Danger Zone</h2>
                <p className="text-xs text-red-600 mb-3">
                    Deleting an exam permanently removes it, its questions, and any sessions
                    hosted from it. This cannot be undone.
                </p>
                <button
                    onClick={() => {
                        if (
                            confirm(
                                `Delete "${exam.title}" permanently? This cannot be undone.`
                            )
                        ) {
                            router.delete(`/exams/${exam.id}`);
                        }
                    }}
                    className="text-sm bg-white border border-red-300 text-red-700 px-4 py-2 rounded-lg font-semibold"
                >
                    Delete Exam
                </button>
            </div>
        </AuthenticatedLayout>
    );
}
