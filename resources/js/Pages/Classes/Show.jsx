import { useState, useRef } from "react";
import { Head, Link, useForm, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Show({ class: schoolClass }) {
    const [copied, setCopied] = useState(false);
    const students = schoolClass.students || [];
    const fileInput = useRef(null);
    const importForm = useForm({ file: null });

    function handleImport(e) {
        e.preventDefault();
        if (!importForm.data.file) return;
        importForm.post(`/classes/${schoolClass.id}/import-students`, {
            onSuccess: () => {
                importForm.reset();
                if (fileInput.current) fileInput.current.value = "";
            },
        });
    }

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
                    <h1 className="font-display text-3xl font-semibold text-[#1B3A34]">{schoolClass.name}</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        {schoolClass.subject || "No subject"}
                        {schoolClass.section ? ` · ${schoolClass.section}` : ""}
                    </p>
                </div>
                <Link
                    href={`/classes/${schoolClass.id}/edit`}
                    className="bg-white border border-[#1B3A34] text-[#1B3A34] px-4 py-2 rounded-lg text-sm font-semibold"
                >
                    Edit Class
                </Link>
            </div>

            <div className="bg-[#1B3A34] text-white rounded-xl p-5 mb-6 flex items-center justify-between">
                <div>
                    <p className="text-xs opacity-80 uppercase tracking-wide">Join Code</p>
                    <p className="text-3xl font-data font-semibold tracking-widest">
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

            <section className="bg-white rounded-xl shadow p-5 mb-6">
                <h2 className="font-semibold text-gray-700 mb-1">Bulk Import Students</h2>
                <p className="text-xs text-gray-400 mb-3">
                    CSV with a header row containing <code className="bg-gray-100 px-1 rounded">name</code> and{" "}
                    <code className="bg-gray-100 px-1 rounded">email</code> columns. New accounts get a
                    temporary password shown once on the results page — there's no email delivery on
                    this network, so you'll need to relay it to each student directly.
                </p>
                <form onSubmit={handleImport} className="flex items-center gap-3">
                    <input
                        ref={fileInput}
                        type="file"
                        accept=".csv,text/csv"
                        onChange={(e) => importForm.setData("file", e.target.files[0] || null)}
                        className="text-sm"
                    />
                    <button
                        type="submit"
                        disabled={!importForm.data.file || importForm.processing}
                        className="bg-[#1B3A34] text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 shrink-0"
                    >
                        {importForm.processing ? "Importing…" : "Import"}
                    </button>
                </form>
                {importForm.errors.file && (
                    <p className="text-red-600 text-sm mt-2">{importForm.errors.file}</p>
                )}
            </section>

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
