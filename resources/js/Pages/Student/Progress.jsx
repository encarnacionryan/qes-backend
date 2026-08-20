import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

const TREND_COPY = {
    up: { label: "Trending up", color: "text-[#8a6d1f]", icon: "↑" },
    down: { label: "Trending down", color: "text-[#C1382C]", icon: "↓" },
    steady: { label: "Holding steady", color: "text-gray-500", icon: "→" },
};

export default function Progress({ history = [], stats }) {
    const hasData = history.length > 0;

    return (
        <AuthenticatedLayout>
            <Head title="My Progress" />

            <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mb-1">My Progress</h1>
            <p className="text-sm text-gray-500 mb-6">Your score across every exam you've completed.</p>

            {!hasData ? (
                <div className="bg-white rounded-2xl shadow p-8 text-center">
                    <p className="text-gray-500 mb-4">
                        Nothing here yet — your score trend will show up once you've completed an exam.
                    </p>
                    <Link href="/student/sessions" className="text-[#1B3A34] font-medium underline">
                        Browse available exams
                    </Link>
                </div>
            ) : (
                <>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <StatCard label="Exams Taken" value={stats.exams_taken} />
                        <StatCard label="Average" value={`${stats.average_percentage}%`} />
                        <StatCard label="Best Score" value={`${stats.best_percentage}%`} accent="text-[#8a6d1f]" />
                        <TrendCard trend={stats.trend} />
                    </div>

                    <section className="bg-white rounded-2xl shadow p-5 mb-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Score Trend</h2>
                        <ProgressChart history={history} />
                    </section>

                    <section className="bg-white rounded-2xl shadow overflow-hidden">
                        <h2 className="font-semibold text-gray-700 px-5 pt-5 pb-3">Exam History</h2>
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                                <tr>
                                    <th className="text-left px-5 py-2">Exam</th>
                                    <th className="text-left px-5 py-2">Date</th>
                                    <th className="text-right px-5 py-2">Score</th>
                                    <th className="text-right px-5 py-2">%</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {[...history].reverse().map((h, i) => (
                                    <tr key={i}>
                                        <td className="px-5 py-3 text-gray-800">{h.exam_title}</td>
                                        <td className="px-5 py-3 text-gray-500">
                                            {new Date(h.submitted_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-5 py-3 text-right font-data text-gray-600">
                                            {h.points_earned}/{h.points_possible}
                                        </td>
                                        <td className="px-5 py-3 text-right font-data font-medium">
                                            {h.percentage}%
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
        <div className="bg-white rounded-2xl shadow p-4">
            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">{label}</p>
            <p className={`font-display text-2xl font-semibold ${accent}`}>{value}</p>
        </div>
    );
}

function TrendCard({ trend }) {
    const copy = trend ? TREND_COPY[trend] : null;
    return (
        <div className="bg-white rounded-2xl shadow p-4">
            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">Trend</p>
            {copy ? (
                <p className={`font-display text-2xl font-semibold ${copy.color}`}>
                    {copy.icon} <span className="text-base align-middle">{copy.label}</span>
                </p>
            ) : (
                <p className="text-sm text-gray-400 mt-2">Not enough attempts yet</p>
            )}
        </div>
    );
}

function ProgressChart({ history }) {
    const width = 700;
    const height = 220;
    const padding = { top: 16, right: 16, bottom: 32, left: 36 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    const points = history.map((h, i) => {
        const x = padding.left + (history.length === 1 ? chartW / 2 : (i / (history.length - 1)) * chartW);
        const y = padding.top + chartH - (h.percentage / 100) * chartH;
        return { x, y, ...h };
    });

    const pathD = points.map((p, i) => `${i === 0 ? "M" : "L"} ${p.x} ${p.y}`).join(" ");
    const gridLines = [0, 25, 50, 75, 100];

    return (
        <div className="overflow-x-auto">
            <svg viewBox={`0 0 ${width} ${height}`} className="w-full" style={{ minWidth: 480 }}>
                {gridLines.map((pct) => {
                    const y = padding.top + chartH - (pct / 100) * chartH;
                    return (
                        <g key={pct}>
                            <line
                                x1={padding.left}
                                y1={y}
                                x2={width - padding.right}
                                y2={y}
                                stroke="#E5E7EB"
                                strokeWidth={1}
                            />
                            <text x={4} y={y + 4} fontSize={10} fill="#9CA3AF" fontFamily="IBM Plex Mono, monospace">
                                {pct}
                            </text>
                        </g>
                    );
                })}

                <path d={pathD} fill="none" stroke="#1B3A34" strokeWidth={2.5} />

                {points.map((p, i) => (
                    <g key={i}>
                        <circle cx={p.x} cy={p.y} r={4} fill="#E0A83E" stroke="#1B3A34" strokeWidth={1.5} />
                        <text
                            x={p.x}
                            y={height - 10}
                            fontSize={9}
                            textAnchor="middle"
                            fill="#9CA3AF"
                            fontFamily="IBM Plex Mono, monospace"
                        >
                            {i + 1}
                        </text>
                    </g>
                ))}
            </svg>
            <p className="text-xs text-gray-400 mt-1">Attempt number, in the order you took each exam.</p>
        </div>
    );
}
