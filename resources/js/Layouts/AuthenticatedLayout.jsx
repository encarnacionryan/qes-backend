import { Link, usePage, router } from "@inertiajs/react";

export default function AuthenticatedLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    function handleLogout(e) {
        e.preventDefault();
        router.post("/logout");
    }

    const isTeacher = user?.role === "teacher";
    const isStudent = user?.role === "student";

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

            <main className="max-w-5xl mx-auto px-4 py-8">{children}</main>
        </div>
    );
}
