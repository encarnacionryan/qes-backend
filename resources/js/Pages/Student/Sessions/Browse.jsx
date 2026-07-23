import { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "../../../Layouts/AuthenticatedLayout";

export default function Browse({ sessions = [] }) {
    const [passwordPromptFor, setPasswordPromptFor] = useState(null);
    const { data, setData, post, processing, errors, reset } = useForm({ password: "" });

    function attemptJoin(session) {
        if (session.requires_password) {
            setPasswordPromptFor(session.id);
            return;
        }
        post(`/student/sessions/${session.id}/join`);
    }

    function submitPassword(e, sessionId) {
        e.preventDefault();
        post(`/student/sessions/${sessionId}/join`, {
            onFinish: () => reset("password"),
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="Browse Exams" />

            <h1 className="text-2xl font-bold text-[#1F3864] mb-6">Available Exams</h1>

            {sessions.length === 0 ? (
                <p className="text-gray-500">
                    No exam sessions are open right now. Ask your teacher to start one.
                </p>
            ) : (
                <ul className="grid md:grid-cols-2 gap-4">
                    {sessions.map((session) => (
                        <li key={session.id} className="bg-white rounded-xl shadow p-5">
                            <div className="flex items-start justify-between">
                                <div>
                                    <h2 className="font-semibold text-gray-800">
                                        {session.exam.title}
                                    </h2>
                                    <p className="text-xs text-gray-400 mt-1">
                                        Hosted by {session.teacher.name} · {session.exam.time_limit_minutes} min ·{" "}
                                        {session.exam.total_points} pts
                                    </p>
                                </div>
                                {session.requires_password && (
                                    <span className="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                                        🔒 Private
                                    </span>
                                )}
                            </div>

                            {passwordPromptFor === session.id ? (
                                <form
                                    onSubmit={(e) => submitPassword(e, session.id)}
                                    className="mt-4 flex gap-2"
                                >
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData("password", e.target.value)}
                                        placeholder="Session password"
                                        className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                        autoFocus
                                    />
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                                    >
                                        Join
                                    </button>
                                </form>
                            ) : (
                                <button
                                    onClick={() => attemptJoin(session)}
                                    className="mt-4 w-full bg-[#1F3864] text-white py-2 rounded-lg text-sm font-semibold"
                                >
                                    Join Exam
                                </button>
                            )}

                            {passwordPromptFor === session.id && errors.password && (
                                <p className="text-red-600 text-sm mt-2">{errors.password}</p>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </AuthenticatedLayout>
    );
}
