import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Analytics({ exam, stats }) {
    const {
        submission_count,
        average_percentage,
        high_percentage,
        low_percentage,
        distribution,
        questions,
    } = stats;

    const maxBucket = Math.max(1, ...distribution.map((d) => d.count));

    return (
        <AuthenticatedLayout>
            <Head title={`Analytics — ${exam.title}`} />

            <Link href={`/exams/${exam.id}/edit`} className="text-sm text-[#1B3A34] underline">
                ← Back to {exam.title}
            </Link>

            <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mt-2 mb-6">Analytics</h1>

            {submission_count === 0 ? (
                <p className="text-gray-500">No graded submissions yet — analytics will appear here once students complete this exam.</p>
            ) : (
                <>
                    {/* Summary cards */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <StatCard label="Submissions" value={submission_count} />
                        <StatCard label="Class Average" value={`${average_percentage}%`} />
                        <StatCard label="Highest" value={`${high_percentage}%`} accent="text-green-600" />
                        <StatCard label="Lowest" value={`${low_percentage}%`} accent="text-red-600" />
                    </div>

                    {/* Distribution */}
                    <section className="bg-white rounded-xl shadow p-5 mb-8">
                        <h2 className="font-semibold text-gray-700 mb-4">Score Distribution</h2>
                        <div className="flex items-end gap-2 h-40">
                            {distribution.map((bucket) => (
                                <div key={bucket.label} className="flex-1 flex flex-col items-center justify-end h-full">
                                    <span className="text-xs text-gray-500 mb-1">
                                        {bucket.count > 0 ? bucket.count : ""}
                                    </span>
                                    <div
                                        className="w-full bg-[#1B3A34] rounded-t"
                                        style={{
                                            height: `${(bucket.count / maxBucket) * 100}%`,
                                            minHeight: bucket.count > 0 ? "4px" : "0px",
                                        }}
                                    />
                                    <span className="text-[10px] text-gray-400 mt-1 rotate-0 text-center">
                                        {bucket.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </section>

                    {/* Per-question breakdown */}
                    <section className="bg-white rounded-xl shadow overflow-hidden">
                        <h2 className="font-semibold text-gray-700 px-5 pt-5">Per-Question Performance</h2>
                        <table className="w-full text-sm mt-3">
                            <thead className="bg-[#EFF5F0] text-gray-500 text-xs uppercase tracking-wide">
                                <tr>
                                    <th className="text-left px-5 py-2">Question</th>
                                    <th className="text-left px-5 py-2">Type</th>
                                    <th className="text-right px-5 py-2">Correct</th>
                                    <th className="text-right px-5 py-2">% Correct</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {questions.map((q, i) => (
                                    <tr key={q.id} className={q.flagged ? "bg-red-50" : ""}>
                                        <td className="px-5 py-3 text-gray-800">
                                            <span className="text-gray-400 mr-1">Q{i + 1}.</span>
                                            {q.prompt}
                                            {q.flagged && (
                                                <span className="ml-2 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                                    Low performing
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 text-gray-500 capitalize">
                                            {q.type.replace("_", " ")}
                                        </td>
                                        <td className="px-5 py-3 text-right text-gray-600">
                                            {q.correct_count}/{q.total_answered}
                                        </td>
                                        <td className="px-5 py-3 text-right font-medium">
                                            {q.percent_correct === null ? "—" : `${q.percent_correct}%`}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </section>
                </>
            )}
        </AuthenticatedLayout>
    );
}

function StatCard({ label, value, accent = "text-gray-800" }) {
    return (
        <div className="bg-white rounded-xl shadow p-4">
            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">{label}</p>
            <p className={`text-2xl font-bold ${accent}`}>{value}</p>
        </div>
    );
}
