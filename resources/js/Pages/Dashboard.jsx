// resources/js/Pages/Dashboard.jsx
//
// Teacher's landing page after login. Backed by Web\DashboardController,
// which passes `classes` (with student counts) and the 5 most recent `exams`.

import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../Layouts/AuthenticatedLayout";

export default function Dashboard({ classes = [], exams = [] }) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-[#1F3864]">Dashboard</h1>
                <div className="flex gap-3">
                    <Link
                        href="/classes/create"
                        className="bg-white border border-[#1F3864] text-[#1F3864] px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        + New Class
                    </Link>
                    <Link
                        href="/exams/create"
                        className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        + New Exam
                    </Link>
                </div>
            </div>

            <div className="grid md:grid-cols-2 gap-6">
                <section className="bg-white rounded-xl shadow p-5">
                    <h2 className="font-semibold text-gray-700 mb-3">Your Classes</h2>
                    {classes.length === 0 ? (
                        <p className="text-sm text-gray-400">
                            No classes yet.{" "}
                            <Link href="/classes/create" className="text-[#1F3864] underline">
                                Create one
                            </Link>
                            .
                        </p>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {classes.map((c) => (
                                <li key={c.id} className="py-3 flex items-center justify-between">
                                    <div>
                                        <Link
                                            href={`/classes/${c.id}`}
                                            className="font-medium text-gray-800 hover:underline"
                                        >
                                            {c.name}
                                        </Link>
                                        <p className="text-xs text-gray-400">
                                            {c.subject || "No subject"} · Code: {c.join_code}
                                        </p>
                                    </div>
                                    <span className="text-sm text-gray-500">
                                        {c.students_count ?? 0} students
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="bg-white rounded-xl shadow p-5">
                    <h2 className="font-semibold text-gray-700 mb-3">Recent Exams</h2>
                    {exams.length === 0 ? (
                        <p className="text-sm text-gray-400">
                            No exams yet.{" "}
                            <Link href="/exams/create" className="text-[#1F3864] underline">
                                Create one
                            </Link>
                            .
                        </p>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {exams.map((exam) => (
                                <li key={exam.id} className="py-3 flex items-center justify-between">
                                    <div>
                                        <Link
                                            href={`/exams/${exam.id}`}
                                            className="font-medium text-gray-800 hover:underline"
                                        >
                                            {exam.title}
                                        </Link>
                                        <p className="text-xs text-gray-400 capitalize">{exam.status}</p>
                                    </div>
                                    <Link
                                        href={`/exams/${exam.id}/sessions`}
                                        className="text-sm text-[#1F3864] underline"
                                    >
                                        Host session
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
