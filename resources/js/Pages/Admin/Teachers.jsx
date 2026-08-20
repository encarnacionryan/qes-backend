import { useState } from "react";
import { Head, useForm, usePage, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Teachers({ teachers = [], students = [], logs = [] }) {
    const { flash } = usePage().props;
    const [showCreate, setShowCreate] = useState(false);
    const [copied, setCopied] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({ name: "", email: "" });

    function handleCreate(e) {
        e.preventDefault();
        post("/admin/teachers", {
            onSuccess: () => {
                reset();
                setShowCreate(false);
            },
        });
    }

    function copyPassword() {
        navigator.clipboard?.writeText(flash.temp_password);
        setCopied(true);
        setTimeout(() => setCopied(false), 1500);
    }

    function disable(teacher) {
        if (!confirm(`Disable ${teacher.name}'s account? They won't be able to log in until re-enabled.`)) return;
        router.post(`/admin/teachers/${teacher.id}/disable`);
    }

    function enable(teacher) {
        router.post(`/admin/teachers/${teacher.id}/enable`);
    }

    function remove(teacher) {
        if (!confirm(`Permanently remove ${teacher.name}'s account? This cannot be undone.`)) return;
        router.delete(`/admin/teachers/${teacher.id}`);
    }

    function resetPassword(user) {
        if (!confirm(`Reset ${user.name}'s password? A new temporary password will be generated.`)) return;
        router.post(`/admin/users/${user.id}/reset-password`);
    }

    return (
        <AuthenticatedLayout>
            <Head title="Admin — Teachers" />

            <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mb-6">User Management</h1>

            {flash?.temp_password && (
                <div className="bg-yellow-50 border border-yellow-300 rounded-xl p-5 mb-6 flex items-center justify-between">
                    <div>
                        <p className="text-sm font-semibold text-yellow-800 mb-1">
                            Temporary password generated — copy it now
                        </p>
                        <p className="text-xs text-yellow-700 mb-2">
                            This is shown only once. There's no email delivery on this local
                            network — relay it to the account holder directly.
                        </p>
                        <p className="font-mono text-lg bg-white border border-yellow-200 rounded px-3 py-1 inline-block">
                            {flash.temp_password}
                        </p>
                    </div>
                    <button
                        onClick={copyPassword}
                        className="bg-yellow-800 text-white px-4 py-2 rounded-lg text-sm font-semibold shrink-0 ml-4"
                    >
                        {copied ? "Copied!" : "Copy"}
                    </button>
                </div>
            )}

            {/* Teachers */}
            <section className="mb-8">
                <div className="flex items-center justify-between mb-3">
                    <h2 className="font-semibold text-gray-700">Teachers</h2>
                    <button
                        onClick={() => setShowCreate((v) => !v)}
                        className="bg-[#1B3A34] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        {showCreate ? "Cancel" : "+ Add Teacher"}
                    </button>
                </div>

                {showCreate && (
                    <form onSubmit={handleCreate} className="bg-white rounded-xl shadow p-5 mb-4 space-y-3 max-w-md">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData("name", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                autoFocus
                            />
                            {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData("email", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                            {errors.email && <p className="text-red-600 text-sm mt-1">{errors.email}</p>}
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1B3A34] text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Creating…" : "Create Teacher Account"}
                        </button>
                    </form>
                )}

                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-[#EFF5F0] text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th className="text-left px-5 py-2">Name</th>
                                <th className="text-left px-5 py-2">Email</th>
                                <th className="text-left px-5 py-2">Status</th>
                                <th className="text-right px-5 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {teachers.map((t) => (
                                <tr key={t.id}>
                                    <td className="px-5 py-3 text-gray-800">
                                        {t.name}
                                        {t.is_lead_teacher && (
                                            <span className="ml-2 text-xs bg-blue-100 text-[#1B3A34] px-2 py-0.5 rounded-full">
                                                Lead
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-5 py-3 text-gray-500">{t.email}</td>
                                    <td className="px-5 py-3">
                                        <span
                                            className={`text-xs px-2 py-0.5 rounded-full ${
                                                t.is_active
                                                    ? "bg-green-100 text-green-700"
                                                    : "bg-gray-200 text-gray-500"
                                            }`}
                                        >
                                            {t.is_active ? "Active" : "Disabled"}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                                        <button
                                            onClick={() => resetPassword(t)}
                                            className="text-[#1B3A34] underline text-sm"
                                        >
                                            Reset Password
                                        </button>
                                        {!t.is_lead_teacher && (
                                            <>
                                                {t.is_active ? (
                                                    <button
                                                        onClick={() => disable(t)}
                                                        className="text-yellow-700 underline text-sm"
                                                    >
                                                        Disable
                                                    </button>
                                                ) : (
                                                    <button
                                                        onClick={() => enable(t)}
                                                        className="text-green-700 underline text-sm"
                                                    >
                                                        Enable
                                                    </button>
                                                )}
                                                <button
                                                    onClick={() => remove(t)}
                                                    className="text-red-600 underline text-sm"
                                                >
                                                    Remove
                                                </button>
                                            </>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>

            {/* Students */}
            <section className="mb-8">
                <h2 className="font-semibold text-gray-700 mb-3">Students</h2>
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-[#EFF5F0] text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th className="text-left px-5 py-2">Name</th>
                                <th className="text-left px-5 py-2">Email</th>
                                <th className="text-right px-5 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {students.map((s) => (
                                <tr key={s.id}>
                                    <td className="px-5 py-3 text-gray-800">{s.name}</td>
                                    <td className="px-5 py-3 text-gray-500">{s.email}</td>
                                    <td className="px-5 py-3 text-right">
                                        <button
                                            onClick={() => resetPassword(s)}
                                            className="text-[#1B3A34] underline text-sm"
                                        >
                                            Reset Password
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {students.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="px-5 py-4 text-gray-400 text-center">
                                        No students yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </section>

            {/* Audit log */}
            <section>
                <h2 className="font-semibold text-gray-700 mb-3">Recent Activity</h2>
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-[#EFF5F0] text-gray-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th className="text-left px-5 py-2">When</th>
                                <th className="text-left px-5 py-2">By</th>
                                <th className="text-left px-5 py-2">Action</th>
                                <th className="text-left px-5 py-2">Target</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {logs.map((log) => (
                                <tr key={log.id}>
                                    <td className="px-5 py-3 text-gray-400 text-xs">
                                        {new Date(log.created_at).toLocaleString()}
                                    </td>
                                    <td className="px-5 py-3 text-gray-600">{log.actor?.name ?? "—"}</td>
                                    <td className="px-5 py-3 text-gray-700 capitalize">
                                        {log.action.replace(/_/g, " ")}
                                    </td>
                                    <td className="px-5 py-3 text-gray-500">
                                        {log.targetUser?.name ?? log.target_label ?? "—"}
                                    </td>
                                </tr>
                            ))}
                            {logs.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-5 py-4 text-gray-400 text-center">
                                        No activity yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </section>
        </AuthenticatedLayout>
    );
}
