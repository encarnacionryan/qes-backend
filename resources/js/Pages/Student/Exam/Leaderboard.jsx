import { Head, Link } from "@inertiajs/react";

const MEDALS = { 1: "🥇", 2: "🥈", 3: "🥉" };

export default function Leaderboard({ exam, entries = [], myStudentId }) {
    return (
        <div className="min-h-screen bg-gray-50 px-4 py-8">
            <Head title={`Leaderboard — ${exam.title}`} />

            <div className="max-w-lg mx-auto">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-1">{exam.title}</h1>
                <p className="text-sm text-gray-500 mb-6">Leaderboard</p>

                <div className="bg-white rounded-xl shadow overflow-hidden mb-4">
                    {entries.map((entry) => {
                        const isMe = entry.student_id === myStudentId;
                        return (
                            <div
                                key={entry.id}
                                className={`flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0 ${
                                    isMe ? "bg-blue-50" : ""
                                }`}
                            >
                                <div className="flex items-center gap-3">
                                    <span className="w-8 text-sm font-semibold text-gray-600">
                                        {MEDALS[entry.rank] || `#${entry.rank}`}
                                    </span>
                                    <span className={`text-sm ${isMe ? "font-semibold text-[#1F3864]" : "text-gray-700"}`}>
                                        {entry.student.name}
                                        {isMe && " (you)"}
                                    </span>
                                </div>
                                <span className="text-sm text-gray-600">{entry.score.percentage}%</span>
                            </div>
                        );
                    })}
                </div>

                <Link
                    href="/student/sessions"
                    className="block text-center bg-white border border-[#1F3864] text-[#1F3864] py-2 rounded-lg text-sm font-semibold"
                >
                    Back to Available Exams
                </Link>
            </div>
        </div>
    );
}
