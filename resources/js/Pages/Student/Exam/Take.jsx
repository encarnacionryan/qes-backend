import { useEffect, useMemo, useRef, useState } from "react";
import { Head, router } from "@inertiajs/react";
import axios from "axios";

function buildInitialAnswers(exam, submission) {
    const map = {};
    for (const q of exam.questions) {
        map[q.id] = q.type === "matching" ? {} : "";
    }
    for (const a of submission.answers || []) {
        if (!a.response) continue;
        if (a.response.choice_id !== undefined) map[a.question_id] = String(a.response.choice_id);
        else if (a.response.answer !== undefined) map[a.question_id] = a.response.answer;
        else if (a.response.pairs) {
            const pairMap = {};
            for (const p of a.response.pairs) pairMap[p.choice_id] = p.match_value;
            map[a.question_id] = pairMap;
        }
    }
    return map;
}

function toResponse(question, value) {
    if (question.type === "mcq") return { choice_id: Number(value) };
    if (question.type === "matching") {
        return {
            pairs: Object.entries(value || {}).map(([choice_id, match_value]) => ({
                choice_id: Number(choice_id),
                match_value,
            })),
        };
    }
    return { answer: value }; 
}

export default function Take({ submission, exam }) {
    const questions = exam.questions || [];
    const [answers, setAnswers] = useState(() => buildInitialAnswers(exam, submission));
    const [savingIds, setSavingIds] = useState(new Set());
    const [submitting, setSubmitting] = useState(false);
    const saveTimers = useRef({});
    const pendingSaves = useRef({});

    const deadline = useMemo(() => {
        return new Date(submission.started_at).getTime() + exam.time_limit_minutes * 60 * 1000;
    }, [submission.started_at, exam.time_limit_minutes]);

    const [remainingMs, setRemainingMs] = useState(() => deadline - Date.now());

    useEffect(() => {
        const interval = setInterval(() => {
            const remaining = deadline - Date.now();
            setRemainingMs(remaining);
            if (remaining <= 0) {
                clearInterval(interval);
                handleSubmit(true);
            }
        }, 1000);
        return () => clearInterval(interval);
    }, [deadline]);

    function saveAnswer(questionId, response) {
        setSavingIds((prev) => new Set(prev).add(questionId));
        const request = axios
            .put(`/student/submissions/${submission.id}/answers`, {
                answers: [{ question_id: questionId, response }],
            })
            .catch(() => 
                return new Promise((resolve) => {
                    setTimeout(() => {
                        axios
                            .put(`/student/submissions/${submission.id}/answers`, {
                                answers: [{ question_id: questionId, response }],
                            })
                            .catch(() => {})
                            .finally(resolve);
                    }, 3000);
                });
            })
            .finally(() => {
                setSavingIds((prev) => {
                    const next = new Set(prev);
                    next.delete(questionId);
                    return next;
                });
            });

        return request;
    }

    function updateAnswer(question, value) {
        setAnswers((prev) => ({ ...prev, [question.id]: value }));

        clearTimeout(saveTimers.current[question.id]);
        pendingSaves.current[question.id] = toResponse(question, value);
        saveTimers.current[question.id] = setTimeout(() => {
            saveAnswer(question.id, pendingSaves.current[question.id]);
            delete pendingSaves.current[question.id];
        }, 600);
    }

    function updateMatchingAnswer(question, choiceId, value) {
        const next = { ...(answers[question.id] || {}), [choiceId]: value };
        updateAnswer(question, next);
    }

    async function flushPendingSaves() {
        const pending = Object.entries(pendingSaves.current);
        if (pending.length === 0) return;

        await Promise.all(
            pending.map(([questionId, response]) => {
                clearTimeout(saveTimers.current[questionId]);
                delete pendingSaves.current[questionId];
                return saveAnswer(Number(questionId), response);
            })
        );
    }

    async function handleSubmit(auto = false) {
        if (submitting) return;
        if (!auto && !confirm("Submit your exam now? You won't be able to change your answers after this.")) {
            return;
        }
        setSubmitting(true);
        await flushPendingSaves();
        router.post(`/student/submissions/${submission.id}/submit`);
    }

    const minutes = Math.max(0, Math.floor(remainingMs / 60000));
    const seconds = Math.max(0, Math.floor((remainingMs % 60000) / 1000));
    const isLowTime = remainingMs < 60000;

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={exam.title} />

            <div
                className={`sticky top-0 z-10 px-4 py-3 flex items-center justify-between text-white ${
                    isLowTime ? "bg-red-600" : "bg-[#1F3864]"
                }`}
            >
                <h1 className="font-semibold truncate">{exam.title}</h1>
                <span className="font-mono text-lg tabular-nums">
                    {String(minutes).padStart(2, "0")}:{String(seconds).padStart(2, "0")}
                </span>
            </div>

            <div className="max-w-2xl mx-auto px-4 py-6 space-y-4">
                {questions.map((q, i) => (
                    <div key={q.id} className="bg-white rounded-xl shadow p-5">
                        <div className="flex items-start justify-between mb-2">
                            <p className="text-xs text-gray-400 uppercase tracking-wide">
                                Question {i + 1} of {questions.length} · {q.points} pt
                                {q.points === 1 ? "" : "s"}
                            </p>
                            {savingIds.has(q.id) && (
                                <span className="text-xs text-gray-400">Saving…</span>
                            )}
                        </div>
                        <p className="text-gray-800 font-medium mb-3">{q.prompt}</p>

                        {q.type === "mcq" && (
                            <div className="space-y-2">
                                {q.choices.map((choice) => (
                                    <label
                                        key={choice.id}
                                        className="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer has-[:checked]:border-[#1F3864] has-[:checked]:bg-blue-50"
                                    >
                                        <input
                                            type="radio"
                                            name={`q-${q.id}`}
                                            checked={answers[q.id] === String(choice.id)}
                                            onChange={() => updateAnswer(q, String(choice.id))}
                                        />
                                        <span className="text-sm">{choice.label}</span>
                                    </label>
                                ))}
                            </div>
                        )}

                        {q.type === "true_false" && (
                            <div className="flex gap-3">
                                {["true", "false"].map((val) => (
                                    <label
                                        key={val}
                                        className="flex-1 flex items-center justify-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer capitalize has-[:checked]:border-[#1F3864] has-[:checked]:bg-blue-50"
                                    >
                                        <input
                                            type="radio"
                                            name={`q-${q.id}`}
                                            checked={answers[q.id] === val}
                                            onChange={() => updateAnswer(q, val)}
                                        />
                                        <span className="text-sm">{val}</span>
                                    </label>
                                ))}
                            </div>
                        )}

                        {q.type === "identification" && (
                            <input
                                type="text"
                                value={answers[q.id] || ""}
                                onChange={(e) => updateAnswer(q, e.target.value)}
                                placeholder="Your answer"
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                        )}

                        {q.type === "matching" && (
                            <div className="space-y-2">
                                {q.choices.map((choice) => (
                                    <div key={choice.id} className="flex items-center gap-3">
                                        <span className="flex-1 text-sm text-gray-700">
                                            {choice.label}
                                        </span>
                                        <input
                                            type="text"
                                            placeholder="Match"
                                            value={(answers[q.id] || {})[choice.id] || ""}
                                            onChange={(e) =>
                                                updateMatchingAnswer(q, choice.id, e.target.value)
                                            }
                                            className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                        />
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                ))}

                <button
                    onClick={() => handleSubmit(false)}
                    disabled={submitting}
                    className="w-full bg-[#1F3864] text-white py-3 rounded-lg font-semibold disabled:opacity-60"
                >
                    {submitting ? "Submitting…" : "Submit Exam"}
                </button>
            </div>
        </div>
    );
}
