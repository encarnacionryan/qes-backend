import { useState } from "react";
import { Head, Link, useForm, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Sessions({ exam, sessions = [] }) {
    const [showForm, setShowForm] = useState(sessions.length === 0);
    const { data, setData, post, processing, errors, reset } = useForm({
        visibility: "public",
        password: "",
        opens_at: "",
        closes_at: "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post(`/exams/${exam.id}/sessions`, {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    }

    function closeSession(session) {
        if (!confirm("Close this session? Students will no longer be able to join or continue.")) return;
        router.post(`/sessions/${session.id}/close`);
    }

    return (
        <AuthenticatedLayout>
            <Head title={`Sessions — ${exam.title}`} />

            <Link href={`/exams/${exam.id}/edit`} className="text-sm text-[#1B3A34] underline">
                ← Back to {exam.title}
            </Link>

            <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mt-2 mb-6">Host a Session</h1>

            {showForm ? (
                <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-6 space-y-4 max-w-md mb-8">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                        <div className="grid grid-cols-2 gap-2">
                            {["public", "private"].map((v) => (
                                <button
                                    key={v}
                                    type="button"
                                    onClick={() => setData("visibility", v)}
                                    className={`py-2 rounded-lg text-sm font-semibold capitalize border ${
                                        data.visibility === v
                                            ? "bg-[#1B3A34] text-white border-[#1B3A34]"
                                            : "bg-white text-gray-600 border-gray-300"
                                    }`}
                                >
                                    {v}
                                </button>
                            ))}
                        </div>
                        <p className="text-xs text-gray-400 mt-2">
                            {data.visibility === "public"
                                ? "Any student can find and join without a password."
                                : "Students need the password below to join."}
                        </p>
                    </div>

                    {data.visibility === "private" && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Session Password
                            </label>
                            <input
                                type="text"
                                value={data.password}
                                onChange={(e) => setData("password", e.target.value)}
                                placeholder="At least 4 characters"
                                className="w-full border border-gray-300 rounded-lg px-3 py-2"
                            />
                            {errors.password && (
                                <p className="text-red-600 text-sm mt-1">{errors.password}</p>
                            )}
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Opens At <span className="text-gray-400">(optional)</span>
                            </label>
                            <input
                                type="datetime-local"
                                value={data.opens_at}
                                onChange={(e) => setData("opens_at", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Closes At <span className="text-gray-400">(optional)</span>
                            </label>
                            <input
                                type="datetime-local"
                                value={data.closes_at}
                                onChange={(e) => setData("closes_at", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                            {errors.closes_at && (
                                <p className="text-red-600 text-sm mt-1">{errors.closes_at}</p>
                            )}
                        </div>
                    </div>
                    <p className="text-xs text-gray-400 -mt-2">
                        Leave both blank to open immediately and stay open until you close it manually.
                    </p>

                    <div className="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1B3A34] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Starting…" : "Start Session"}
                        </button>
                        {sessions.length > 0 && (
                            <button
                                type="button"
                                onClick={() => setShowForm(false)}
                                className="text-sm text-gray-500 underline"
                            >
                                Cancel
                            </button>
                        )}
                    </div>
                </form>
            ) : (
                <button
                    onClick={() => setShowForm(true)}
                    className="mb-8 bg-[#1B3A34] text-white px-5 py-2 rounded-lg text-sm font-semibold"
                >
                    + Start Another Session
                </button>
            )}

            <h2 className="font-semibold text-gray-700 mb-3">Session History</h2>
            {sessions.length === 0 ? (
                <p className="text-sm text-gray-400">No sessions started yet.</p>
            ) : (
                <ul className="space-y-3">
                    {sessions.map((session) => (
                        <li
                            key={session.id}
                            className="bg-white rounded-xl shadow p-4 flex items-center justify-between"
                        >
                            <div>
                                <div className="flex items-center gap-2 flex-wrap">
                                    <span className="text-sm font-medium capitalize">{session.visibility}</span>
                                    <span
                                        className={`text-xs px-2 py-0.5 rounded-full ${
                                            session.status === "open"
                                                ? "bg-green-100 text-green-700"
                                                : "bg-gray-100 text-gray-500"
                                        }`}
                                    >
                                        {session.status}
                                    </span>
                                    <span className="text-xs bg-blue-50 text-[#1B3A34] px-2 py-0.5 rounded-full">
                                        {session.submissions_count}{" "}
                                        student{session.submissions_count === 1 ? "" : "s"} joined
                                    </span>
                                </div>
                                <p className="text-xs text-gray-400 mt-1">
                                    Started {new Date(session.created_at).toLocaleString()}
                                    {session.opens_at && (
                                        <> · Opens {new Date(session.opens_at).toLocaleString()}</>
                                    )}
                                    {session.closes_at && (
                                        <> · Closes {new Date(session.closes_at).toLocaleString()}</>
                                    )}
                                </p>
                            </div>
                            {session.status === "open" && (
                                <button
                                    onClick={() => closeSession(session)}
                                    className="text-sm text-red-600 underline"
                                >
                                    Close Session
                                </button>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </AuthenticatedLayout>
    );
}
