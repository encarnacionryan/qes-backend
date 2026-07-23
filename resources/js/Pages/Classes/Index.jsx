// resources/js/Pages/Classes/Index.jsx
//
// Sprint 2, QES-14/QES-18. Backed by Web\SchoolClassController::index.

import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Index({ classes = [] }) {
    function archive(schoolClass) {
        if (!confirm(`Archive "${schoolClass.name}"? Students will no longer see it as active.`)) {
            return;
        }
        router.post(`/classes/${schoolClass.id}/archive`);
    }

    return (
        <AuthenticatedLayout>
            <Head title="Classes" />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-[#1F3864]">Your Classes</h1>
                <Link
                    href="/classes/create"
                    className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    + New Class
                </Link>
            </div>

            {classes.length === 0 ? (
                <div className="bg-white rounded-xl shadow p-8 text-center">
                    <p className="text-gray-500 mb-4">You haven't created any classes yet.</p>
                    <Link
                        href="/classes/create"
                        className="text-[#1F3864] underline font-medium"
                    >
                        Create your first class
                    </Link>
                </div>
            ) : (
                <ul className="grid md:grid-cols-2 gap-4">
                    {classes.map((c) => (
                        <li key={c.id} className="bg-white rounded-xl shadow p-5">
                            <div className="flex items-start justify-between">
                                <div>
                                    <Link
                                        href={`/classes/${c.id}`}
                                        className="font-semibold text-gray-800 hover:underline"
                                    >
                                        {c.name}
                                    </Link>
                                    <p className="text-xs text-gray-400 mt-1">
                                        {c.subject || "No subject"}
                                        {c.section ? ` · ${c.section}` : ""}
                                    </p>
                                </div>
                                {c.is_archived && (
                                    <span className="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">
                                        Archived
                                    </span>
                                )}
                            </div>

                            <div className="mt-4 flex items-center justify-between text-sm">
                                <span className="text-gray-500">
                                    {c.students_count ?? 0} students
                                </span>
                                <span className="font-mono bg-gray-100 px-2 py-1 rounded text-[#1F3864]">
                                    {c.join_code}
                                </span>
                            </div>

                            <div className="mt-4 flex gap-3 text-sm">
                                <Link href={`/classes/${c.id}`} className="text-[#1F3864] underline">
                                    View roster
                                </Link>
                                <Link href={`/classes/${c.id}/edit`} className="text-[#1F3864] underline">
                                    Edit
                                </Link>
                                {!c.is_archived && (
                                    <button
                                        onClick={() => archive(c)}
                                        className="text-gray-500 underline ml-auto"
                                    >
                                        Archive
                                    </button>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AuthenticatedLayout>
    );
}
