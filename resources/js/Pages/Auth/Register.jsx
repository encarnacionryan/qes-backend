// resources/js/Pages/Auth/Register.jsx
//
// Sprint 1, QES-7. PWA update: registration now includes a role toggle
// since both teachers and students register through this same app —
// see Web\AuthController::register, which validates `role` in [teacher, student].

import { Head, Link, useForm } from "@inertiajs/react";

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        role: "student",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post("/register");
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
            <Head title="Register" />

            <div className="w-full max-w-sm bg-white rounded-xl shadow p-8">
                <h1 className="text-2xl font-bold text-[#1F3864] mb-1">Create an account</h1>
                <p className="text-sm text-gray-500 mb-6">Join Q.E.S as a teacher or student</p>

                {/* Role toggle */}
                <div className="grid grid-cols-2 gap-2 mb-5">
                    {["student", "teacher"].map((role) => (
                        <button
                            key={role}
                            type="button"
                            onClick={() => setData("role", role)}
                            className={`py-2 rounded-lg text-sm font-semibold capitalize border ${
                                data.role === role
                                    ? "bg-[#1F3864] text-white border-[#1F3864]"
                                    : "bg-white text-gray-600 border-gray-300"
                            }`}
                        >
                            {role}
                        </button>
                    ))}
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                            autoFocus
                        />
                        {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                        {errors.email && <p className="text-red-600 text-sm mt-1">{errors.email}</p>}
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
                        />
                        {errors.password && (
                            <p className="text-red-600 text-sm mt-1">{errors.password}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password
                        </label>
                        <input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData("password_confirmation", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-[#1F3864] text-white rounded-lg py-2 font-semibold disabled:opacity-60"
                    >
                        {processing ? "Creating account…" : `Register as ${data.role}`}
                    </button>
                </form>

                <p className="text-sm text-gray-500 mt-6 text-center">
                    Already have an account?{" "}
                    <Link href="/login" className="text-[#1F3864] underline">
                        Log in
                    </Link>
                </p>
            </div>
        </div>
    );
}
