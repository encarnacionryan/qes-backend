import { useEffect, useState } from "react";
import { Link, usePage, router } from "@inertiajs/react";

export default function AuthenticatedLayout({ children }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;
    const [dismissed, setDismissed] = useState(false);

    useEffect(() => {
        setDismissed(false);
    }, [flash?.success, flash?.error]);

    function handleLogout(e) {
        e.preventDefault();
        router.post("/logout");
    }

    const isTeacher = user?.role === "teacher";
    const message = flash?.error || flash?.success;
    const isError = !!flash?.error;

    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-[#1F3864] text-white">
                <div className="max-w-5xl mx-auto px-4 flex items-center justify-between h-14">
                    <div className="flex items-center gap-6">
                        <Link href="/" className="font-bold text-lg">
                            Q.E.S
                        </Link>
                        {isTeacher ? (
                            <>
                                <Link href="/dashboard" className="text-sm hover:underline">
                                    Dashboard
                                </Link>
                                <Link href="/classes" className="text-sm hover:underline">
                                    Classes
                                </Link>
                                <Link href="/exams" className="text-sm hover:underline">
                                    Exams
                                </Link>
                            </>
                        ) : (
                            <Link href="/student/sessions" className="text-sm hover:underline">
                                Browse Exams
                            </Link>
                        )}
                    </div>

                    <div className="flex items-center gap-4">
                        {user && <span className="text-sm opacity-80">{user.name}</span>}
                        <button onClick={handleLogout} className="text-sm hover:underline">
                            Log out
                        </button>
                    </div>
                </div>
            </nav>

            {message && !dismissed && (
                <div
                    className={`px-4 py-3 text-sm flex items-center justify-between ${
                        isError ? "bg-red-50 text-red-700 border-b border-red-200" : "bg-green-50 text-green-700 border-b border-green-200"
                    }`}
                >
                    <span className="max-w-5xl mx-auto w-full">{message}</span>
                    <button
                        onClick={() => setDismissed(true)}
                        className="text-xs opacity-60 hover:opacity-100 ml-4"
                    >
                        ✕
                    </button>
                </div>
            )}

            <main className="max-w-5xl mx-auto px-4 py-8">{children}</main>
        </div>
    );
}
