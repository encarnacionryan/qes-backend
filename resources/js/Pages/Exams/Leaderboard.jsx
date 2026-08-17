import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

const MEDALS = { 1: "🥇", 2: "🥈", 3: "🥉" };

export default function Leaderboard({ exam, entries = [] }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Leaderboard — ${exam.title}`} />

            <Link href={`/exams/${exam.id}/edit`} className="text-sm text-[#1F3864] underline">
                ← Back to {exam.title}
            </Link>

            <div className="flex items-center justify-between mt-2 mb-6">
                <h1 className="text-2xl font-bold text-[#1F3864]">Leaderboard</h1>
                {entries.length > 0 && (
                    <a
                        href={`/exams/${exam.id}/gradebook.csv`}
                        className="bg-white border border-[#1F3864] text-[#1F3864] px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Export CSV
                    </a>
                )}
            </div>

            {entries.length === 0 ? (
                <p className="text-gray-500">No students have completed this exam yet.</p>
            ) : (
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th className="text-left px-5 py-3">Rank</th>
                                <th className="text-left px-5 py-3">Student</th>
                                <th className="text-right px-5 py-3">Score</th>
                                <th className="text-right px-5 py-3">%</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {entries.map((entry) => (
                                <tr key={entry.id}>
                                    <td className="px-5 py-3 font-semibold text-gray-700">
                                        {MEDALS[entry.rank] || `#${entry.rank}`}
                                    </td>
                                    <td className="px-5 py-3 text-gray-800">{entry.student.name}</td>
                                    <td className="px-5 py-3 text-right text-gray-600">
                                        {entry.score.total_points_earned}/{entry.score.total_points_possible}
                                    </td>
                                    <td className="px-5 py-3 text-right text-gray-600">
                                        {entry.score.percentage}%
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
