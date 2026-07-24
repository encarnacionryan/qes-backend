
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout";

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: "",
        description: "",
        time_limit_minutes: 30,
    });

    function handleSubmit(e) {
        e.preventDefault();
        post("/exams");
    }

    return (
        <AuthenticatedLayout>
            <Head title="New Exam" />

            <div className="max-w-lg">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-6">Create an Exam</h1>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl shadow p-6 space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Exam Title
                        </label>
                        <input
                            type="text"
                            value={data.title}
                            onChange={(e) => setData("title", e.target.value)}
                            placeholder="e.g. Chapter 4 Quiz"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                            autoFocus
                        />
                        {errors.title && <p className="text-red-600 text-sm mt-1">{errors.title}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Description <span className="text-gray-400">(optional)</span>
                        </label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData("description", e.target.value)}
                            rows={3}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Time Limit (minutes)
                        </label>
                        <input
                            type="number"
                            min={1}
                            value={data.time_limit_minutes}
                            onChange={(e) => setData("time_limit_minutes", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                        {errors.time_limit_minutes && (
                            <p className="text-red-600 text-sm mt-1">{errors.time_limit_minutes}</p>
                        )}
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1F3864] text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
                        >
                            {processing ? "Creating…" : "Create & Add Questions"}
                        </button>
                        <Link href="/exams" className="text-sm text-gray-500 underline">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
