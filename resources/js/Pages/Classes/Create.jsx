// resources/js/Pages/Classes/Create.jsx
//
// Sprint 2, QES-14. Backed by Web\SchoolClassController::store.
// join_code is auto-generated server-side (FR-2.2) — nothing to enter here.

import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        subject: "",
        section: "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post("/classes");
    }

    return (
        <AuthenticatedLayout>
            <Head title="New Class" />

            <div className="max-w-lg">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-6">Create a Class</h1>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-6 space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Class Name
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            placeholder="e.g. Grade 10 - Science"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                            autoFocus
                        />
                        {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Subject <span className="text-gray-400">(optional)</span>
                        </label>
                        <input
                            type="text"
                            value={data.subject}
                            onChange={(e) => setData("subject", e.target.value)}
                            placeholder="e.g. Biology"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Section <span className="text-gray-400">(optional)</span>
                        </label>
                        <input
                            type="text"
                            value={data.section}
                            onChange={(e) => setData("section", e.target.value)}
                            placeholder="e.g. Section A"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1F3864] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Creating…" : "Create Class"}
                        </button>
                        <Link href="/classes" className="text-sm text-gray-500 underline">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
