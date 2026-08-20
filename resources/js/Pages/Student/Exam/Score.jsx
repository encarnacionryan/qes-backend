import { Head, Link } from "@inertiajs/react";

export default function Score({ submission }) {
    const score = submission.score;
    const answers = submission.answers || [];

    return (
        <div className="min-h-screen bg-[#EFF5F0] flex items-center justify-center px-4 py-10">
            <Head title="Your Score" />

            <div className="w-full max-w-md">
                <div className="relative bg-[#1B3A34] text-white rounded-2xl p-8 text-center mb-4 overflow-hidden">
                    <div className="absolute -top-2 -right-2 text-[#E0A83E] grade-stamp text-xs">
                        Graded
                    </div>
                    <p className="font-data text-xs tracking-widest text-white/60 uppercase mb-2">
                        Your Score
                    </p>
                    <p className="font-display text-6xl font-semibold">
                        {score.total_points_earned}
                        <span className="text-2xl text-white/60">/{score.total_points_possible}</span>
                    </p>
                    <p className="font-data text-lg mt-2 text-white/90">{score.percentage}%</p>
                </div>

                <div className="bg-white rounded-2xl shadow p-5 mb-4">
                    <h2 className="font-semibold text-gray-700 mb-3">
                        {answers.length} Question{answers.length === 1 ? "" : "s"}
                    </h2>
                    <ul className="divide-y divide-gray-100">
                        {answers.map((a, i) => {

                            const earned = Number(a.points_earned);
                            const isPartial = a.is_correct === false && earned > 0;

                            return (
                                <li key={a.id} className="py-2 flex items-center justify-between text-sm">
                                    <span className="text-gray-600">Question {i + 1}</span>
                                    <span
                                        className={`font-data ${
                                            a.is_correct === true
                                                ? "text-[#8a6d1f] font-medium"
                                                : isPartial
                                                  ? "text-amber-600 font-medium"
                                                  : a.is_correct === false
                                                    ? "text-[#C1382C] font-medium"
                                                    : "text-gray-400"
                                        }`}
                                    >
                                        {a.is_correct === true
                                            ? `✓ ${earned} pt`
                                            : isPartial
                                              ? `± ${earned} pt`
                                              : a.is_correct === false
                                                ? "✕ 0 pt"
                                                : "Pending review"}
                                    </span>
                                </li>
                            );
                        })}
                    </ul>
                </div>

                <Link
                    href={`/student/exams/${submission.exam_id}/leaderboard`}
                    className="block text-center bg-[#1B3A34] text-white py-2 rounded-lg text-sm font-semibold mb-3 hover:bg-[#0F2621] transition-colors"
                >
                    View Leaderboard
                </Link>

                <Link
                    href="/student/sessions"
                    className="block text-center bg-white border border-[#1B3A34] text-[#1B3A34] py-2 rounded-lg text-sm font-semibold"
                >
                    Back to Available Exams
                </Link>
            </div>
        </div>
    );
}
