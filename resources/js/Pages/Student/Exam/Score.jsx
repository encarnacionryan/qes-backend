
import { Head, Link } from "@inertiajs/react";

export default function Score({ submission }) {
    const score = submission.score;
    const answers = submission.answers || [];

    return (
        <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
            <Head title="Your Score" />

            <div className="w-full max-w-md">
                <div className="bg-[#1F3864] text-white rounded-xl p-8 text-center mb-4">
                    <p className="text-sm opacity-80 uppercase tracking-wide mb-1">Your Score</p>
                    <p className="text-5xl font-bold">
                        {score.total_points_earned}
                        <span className="text-2xl opacity-70">/{score.total_points_possible}</span>
                    </p>
                    <p className="text-lg mt-2 opacity-90">{score.percentage}%</p>
                </div>

                <div className="bg-white rounded-xl shadow p-5 mb-4">
                    <h2 className="font-semibold text-gray-700 mb-3">
                        {answers.length} Question{answers.length === 1 ? "" : "s"}
                    </h2>
                    <ul className="divide-y divide-gray-100">
                        {answers.map((a, i) => (
                            <li key={a.id} className="py-2 flex items-center justify-between text-sm">
                                <span className="text-gray-600">Question {i + 1}</span>
                                <span
                                    className={
                                        a.is_correct === true
                                            ? "text-green-600 font-medium"
                                            : a.is_correct === false
                                              ? "text-red-600 font-medium"
                                              : "text-gray-400"
                                    }
                                >
                                    {a.is_correct === true
                                        ? `✓ ${a.points_earned} pt`
                                        : a.is_correct === false
                                          ? "✕ 0 pt"
                                          : "Pending review"}
                                </span>
                            </li>
                        ))}
                    </ul>
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
