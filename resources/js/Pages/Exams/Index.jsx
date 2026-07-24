
import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

const STATUS_STYLES = {
    draft: "bg-gray-100 text-gray-600",
    published: "bg-green-100 text-green-700",
    closed: "bg-gray-200 text-gray-500",
};

export default function Index({ exams = [] }) {
    return (
        <AuthenticatedLayout>
            <Head title="Exams" />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-[#1F3864]">Your Exams</h1>
                <Link
                    href="/exams/create"
                    className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    + New Exam
                </Link>
            </div>

            {exams.length === 0 ? (
                <div className="bg-white rounded-xl shadow p-8 text-center">
                    <p className="text-gray-500 mb-4">You haven't created any exams yet.</p>
                    <Link href="/exams/create" className="text-[#1F3864] underline font-medium">
                        Create your first exam
                    </Link>
                </div>
            ) : (
                <ul className="grid md:grid-cols-2 gap-4">
                    {exams.map((exam) => (
                        <li key={exam.id} className="bg-white rounded-xl shadow p-5">
                            <div className="flex items-start justify-between">
                                <Link
                                    href={`/exams/${exam.id}/edit`}
                                    className="font-semibold text-gray-800 hover:underline"
                                >
                                    {exam.title}
                                </Link>
                                <span
                                    className={`text-xs px-2 py-1 rounded-full capitalize ${STATUS_STYLES[exam.status] || ""}`}
                                >
                                    {exam.status}
                                </span>
                            </div>
                            <p className="text-xs text-gray-400 mt-1">
                                {exam.time_limit_minutes} min · {exam.total_points} pts
                            </p>

                            <div className="mt-4 flex gap-3 text-sm">
                                <Link href={`/exams/${exam.id}/edit`} className="text-[#1F3864] underline">
                                    Edit Questions
                                </Link>
                                <Link href={`/exams/${exam.id}/sessions`} className="text-[#1F3864] underline">
                                    Host Session
                                </Link>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AuthenticatedLayout>
    );
}
