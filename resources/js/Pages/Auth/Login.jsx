// resources/js/Pages/Auth/Login.jsx
//
// Sprint 1, QES-8/QES-9. One login form for both roles — the backend
// (Web\AuthController::store) already knows the user's role from the DB
// and redirects to /dashboard (teacher) or /student/sessions (student)
// after a successful login, so this page doesn't need a role toggle.

import { Head, Link, useForm } from "@inertiajs/react";

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post("/login");
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
            <Head title="Log In" />

            <div className="w-full max-w-sm bg-white rounded-xl shadow p-8">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-1">Q.E.S</h1>
                <p className="text-sm text-gray-500 mb-6">Log in to your account</p>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                            autoComplete="email"
                            autoFocus
                        />
                        {errors.email && (
                            <p className="text-red-600 text-sm mt-1">{errors.email}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) => setData("password", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                            autoComplete="current-password"
                        />
                        {errors.password && (
                            <p className="text-red-600 text-sm mt-1">{errors.password}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-[#1F3864] text-white rounded-lg py-2 font-semibold disabled:opacity-60"
                    >
                        {processing ? "Logging in…" : "Log In"}
                    </button>
                </form>

                <p className="text-sm text-gray-500 mt-6 text-center">
                    Don't have an account?{" "}
                    <Link href="/register" className="text-[#1F3864] underline">
                        Register
                    </Link>
                </p>
            </div>
        </div>
    );
}
