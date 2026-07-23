// resources/js/Pages/Classes/Edit.jsx
//
// Sprint 2, QES-14 (edit), QES-18 (archive/delete). Backed by
// Web\SchoolClassController::update / archive / destroy.

import { Head, Link, useForm, router } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Edit({ class: schoolClass }) {
    const { data, setData, put, processing, errors } = useForm({
        name: schoolClass.name,
        subject: schoolClass.subject || "",
        section: schoolClass.section || "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        put(`/classes/${schoolClass.id}`);
    }

    function handleDelete() {
        if (
            !confirm(
                `Delete "${schoolClass.name}" permanently? This cannot be undone and will remove the whole roster.`
            )
        ) {
            return;
        }
        router.delete(`/classes/${schoolClass.id}`);
    }

    return (
        <AuthenticatedLayout>
            <Head title={`Edit ${schoolClass.name}`} />

            <div className="max-w-lg">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-6">Edit Class</h1>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-6 space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Class Name
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                        {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Subject
                        </label>
                        <input
                            type="text"
                            value={data.subject}
                            onChange={(e) => setData("subject", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Section
                        </label>
                        <input
                            type="text"
                            value={data.section}
                            onChange={(e) => setData("section", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1F3864] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Saving…" : "Save Changes"}
                        </button>
                        <Link href={`/classes/${schoolClass.id}`} className="text-sm text-gray-500 underline">
                            Cancel
                        </Link>
                    </div>
                </form>

                <div className="mt-6 bg-red-50 border border-red-100 rounded-xl p-5">
                    <h2 className="text-sm font-semibold text-red-700 mb-1">Danger Zone</h2>
                    <p className="text-xs text-red-600 mb-3">
                        Deleting a class permanently removes its roster and cannot be undone.
                    </p>
                    <button
                        onClick={handleDelete}
                        className="text-sm bg-white border border-red-300 text-red-700 px-4 py-2 rounded-lg font-semibold"
                    >
                        Delete Class
                    </button>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
