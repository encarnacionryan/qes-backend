import { useState } from "react";
import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function ImportResults({ class: schoolClass, created = [], enrolled = [], errors = [] }) {
    const [copied, setCopied] = useState(false);

    function copyAllCredentials() {
        const text = created.map((s) => `${s.name} <${s.email}> — ${s.password}`).join("\n");
        navigator.clipboard?.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 1500);
    }

    return (
        <AuthenticatedLayout>
            <Head title="Import Results" />

            <Link href={`/classes/${schoolClass.id}`} className="text-sm text-[#1B3A34] underline">
                ← Back to {schoolClass.name}
            </Link>

            <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mt-2 mb-6">Import Results</h1>

            <div className="grid grid-cols-3 gap-4 mb-6">
                <div className="bg-white rounded-xl shadow p-4">
                    <p className="text-xs text-gray-400 uppercase tracking-wide">New Accounts</p>
                    <p className="text-2xl font-bold text-green-600">{created.length}</p>
                </div>
                <div className="bg-white rounded-xl shadow p-4">
                    <p className="text-xs text-gray-400 uppercase tracking-wide">Existing, Enrolled</p>
                    <p className="font-display text-3xl font-semibold text-[#1B3A34]">{enrolled.length}</p>
                </div>
                <div className="bg-white rounded-xl shadow p-4">
                    <p className="text-xs text-gray-400 uppercase tracking-wide">Skipped</p>
                    <p className="text-2xl font-bold text-red-500">{errors.length}</p>
                </div>
            </div>

            {created.length > 0 && (
                <section className="bg-yellow-50 border border-yellow-300 rounded-xl p-5 mb-6">
                    <div className="flex items-center justify-between mb-3">
                        <p className="text-sm font-semibold text-yellow-800">
                            New student logins — copy these now, shown only once
                        </p>
                        <button
                            onClick={copyAllCredentials}
                            className="bg-yellow-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shrink-0"
                        >
                            {copied ? "Copied!" : "Copy All"}
                        </button>
                    </div>
                    <div className="bg-white rounded-lg overflow-hidden">
                        <table className="w-full text-sm">
                            <thead className="bg-[#EFF5F0] text-gray-500 text-xs uppercase tracking-wide">
                                <tr>
                                    <th className="text-left px-4 py-2">Name</th>
                                    <th className="text-left px-4 py-2">Email</th>
                                    <th className="text-left px-4 py-2">Temp Password</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {created.map((s, i) => (
                                    <tr key={i}>
                                        <td className="px-4 py-2 text-gray-800">{s.name}</td>
                                        <td className="px-4 py-2 text-gray-500">{s.email}</td>
                                        <td className="px-4 py-2 font-mono">{s.password}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            )}

            {enrolled.length > 0 && (
                <section className="bg-white rounded-xl shadow p-5 mb-6">
                    <h2 className="font-semibold text-gray-700 mb-3">
                        Existing Accounts Enrolled ({enrolled.length})
                    </h2>
                    <ul className="text-sm text-gray-600 space-y-1">
                        {enrolled.map((s, i) => (
                            <li key={i}>
                                {s.name} — {s.email}
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            {errors.length > 0 && (
                <section className="bg-white rounded-xl shadow p-5">
                    <h2 className="font-semibold text-gray-700 mb-3">Skipped Rows ({errors.length})</h2>
                    <table className="w-full text-sm">
                        <thead className="text-gray-400 text-xs uppercase tracking-wide">
                            <tr>
                                <th className="text-left py-1">Row</th>
                                <th className="text-left py-1">Name</th>
                                <th className="text-left py-1">Email</th>
                                <th className="text-left py-1">Reason</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {errors.map((e, i) => (
                                <tr key={i}>
                                    <td className="py-1.5 text-gray-400">{e.row}</td>
                                    <td className="py-1.5 text-gray-700">{e.name}</td>
                                    <td className="py-1.5 text-gray-500">{e.email}</td>
                                    <td className="py-1.5 text-red-600">{e.reason}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </section>
            )}
        </AuthenticatedLayout>
    );
}
