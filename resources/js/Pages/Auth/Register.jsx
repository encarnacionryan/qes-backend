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
        <div className="min-h-screen flex items-center justify-center bg-[#EFF5F0] px-4 py-8">
            <Head title="Register" />

            <div className="w-full max-w-sm">
                <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div className="h-2 bg-[#1B3A34]" />
                    <div className="p-8">
                        <p className="font-data text-xs tracking-widest text-[#1B3A34]/60 uppercase mb-1">
                            Quiz &amp; Exam System
                        </p>
                        <h1 className="font-display text-3xl font-semibold text-[#1B3A34] mb-6">
                            Create an account
                        </h1>

                        <div className="grid grid-cols-2 gap-2 mb-6">
                            {["student", "teacher"].map((role) => (
                                <button
                                    key={role}
                                    type="button"
                                    onClick={() => setData("role", role)}
                                    className={`py-2 rounded-lg text-sm font-semibold capitalize border transition-colors ${
                                        data.role === role
                                            ? "bg-[#1B3A34] text-white border-[#1B3A34]"
                                            : "bg-white text-gray-600 border-gray-300 hover:border-[#1B3A34]/40"
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
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1B3A34]"
                                    autoFocus
                                />
                                {errors.name && <p className="text-[#C1382C] text-sm mt-1">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1B3A34]"
                                />
                                {errors.email && <p className="text-[#C1382C] text-sm mt-1">{errors.email}</p>}
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
                                />
                                {errors.password && (
                                    <p className="text-[#C1382C] text-sm mt-1">{errors.password}</p>
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
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1B3A34]"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-[#1B3A34] text-white rounded-lg py-2.5 font-semibold hover:bg-[#0F2621] transition-colors disabled:opacity-60 capitalize"
                            >
                                {processing ? "Creating account…" : `Register as ${data.role}`}
                            </button>
                        </form>
                    </div>
                </div>

                <p className="text-sm text-gray-500 mt-6 text-center">
                    Already have an account?{" "}
                    <Link href="/login" className="text-[#1B3A34] font-medium underline">
                        Log in
                    </Link>
                </p>
            </div>
        </div>
    );
}
