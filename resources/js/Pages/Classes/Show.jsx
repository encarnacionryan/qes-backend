// resources/js/Pages/Classes/Show.jsx
//
// Sprint 2, QES-17. Backed by Web\SchoolClassController::show, which
// eager-loads `students`. Join code is shown large/copyable since it's
// what the teacher actually reads aloud or writes on the board.

import { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Show({ class: schoolClass }) {
    const [copied, setCopied] = useState(false);
    const students = schoolClass.students || [];

    function copyCode() {
        navigator.clipboard?.writeText(schoolClass.join_code);
        setCopied(true);
        setTimeout(() => setCopied(false), 1500);
    }

    function removeStudent(student) {
        if (!confirm(`Remove ${student.name} from this class?`)) return;
        router.delete(`/classes/${schoolClass.id}/students/${student.id}`);
    }

    return (
        <AuthenticatedLayout>
            <Head title={schoolClass.name} />

            <div className="flex items-start justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-[#1F3864]">{schoolClass.name}</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {schoolClass.subject || "No subject"}
                        {schoolClass.section ? ` · ${schoolClass.section}` : ""}
                    </p>
                </div>
                <Link
                    href={`/classes/${schoolClass.id}/edit`}
                    className="bg-white border border-[#1F3864] text-[#1F3864] px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    Edit Class
                </Link>
            </div>

            <div className="bg-[#1F3864] text-white rounded-xl p-5 mb-6 flex items-center justify-between">
                <div>
                    <p className="text-xs opacity-80 uppercase tracking-wide">Join Code</p>
                    <p className="text-3xl font-mono font-bold tracking-widest">
                        {schoolClass.join_code}
                    </p>
                </div>
                <button
                    onClick={copyCode}
                    className="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    {copied ? "Copied!" : "Copy"}
                </button>
            </div>

            <section className="bg-white rounded-xl shadow p-5">
                <h2 className="font-semibold text-gray-700 mb-4">
                    Roster ({students.length} student{students.length === 1 ? "" : "s"})
                </h2>

                {students.length === 0 ? (
                    <p className="text-sm text-gray-400">
                        No students yet. Share the join code above to get started.
                    </p>
                ) : (
                    <ul className="divide-y divide-gray-100">
                        {students.map((student) => (
                            <li key={student.id} className="py-3 flex items-center justify-between">
                                <div>
                                    <p className="font-medium text-gray-800">{student.name}</p>
                                    <p className="text-xs text-gray-400">{student.email}</p>
                                </div>
                                <button
                                    onClick={() => removeStudent(student)}
                                    className="text-sm text-red-600 hover:underline"
                                >
                                    Remove
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </AuthenticatedLayout>
    );
}
