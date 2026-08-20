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
        <div className="min-h-screen flex items-center justify-center bg-[#EFF5F0] px-4">
            <Head title="Log In" />

            <div className="w-full max-w-sm">
                <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div className="h-2 bg-[#1B3A34]" />
                    <div className="p-8">
                        <p className="font-data text-xs tracking-widest text-[#1B3A34]/60 uppercase mb-1">
                            Quiz &amp; Exam System
                        </p>
                        <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mb-6">
                            Welcome back
                        </h1>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1B3A34]"
                                    autoComplete="email"
                                    autoFocus
                                />
                                {errors.email && (
                                    <p className="text-[#C1382C] text-sm mt-1">{errors.email}</p>
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
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1B3A34]"
                                    autoComplete="current-password"
                                />
                                {errors.password && (
                                    <p className="text-[#C1382C] text-sm mt-1">{errors.password}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-[#1B3A34] text-white rounded-lg py-2.5 font-semibold hover:bg-[#0F2621] transition-colors disabled:opacity-60"
                            >
                                {processing ? "Logging in…" : "Log In"}
                            </button>
                        </form>
                    </div>
                </div>

                <p className="text-sm text-gray-500 mt-6 text-center">
                    Don't have an account?{" "}
                    <Link href="/register" className="text-[#1B3A34] font-medium underline">
                        Register
                    </Link>
                </p>
            </div>
        </div>
    );
}
