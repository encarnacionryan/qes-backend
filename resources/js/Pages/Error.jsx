import { Head, Link } from "@inertiajs/react";

const MESSAGES = {
    403: {
        title: "Access Denied",
        body: "You don't have permission to view this page.",
    },
    404: {
        title: "Page Not Found",
        body: "The page you're looking for doesn't exist or may have moved.",
    },
    419: {
        title: "Session Expired",
        body: "Your session timed out, likely from being idle too long. Please refresh and log in again — if you were mid-exam, your answers up to your last save are safe.",
    },
    500: {
        title: "Something Went Wrong",
        body: "An unexpected error occurred on the server. If this happened during an exam, your progress up to your last autosave is safe — try refreshing.",
    },
    503: {
        title: "Service Unavailable",
        body: "The server is temporarily down for maintenance. Please try again in a moment.",
    },
};

export default function Error({ status }) {
    const { title, body } = MESSAGES[status] || {
        title: "Unexpected Error",
        body: "Something went wrong. Please try again.",
    };

    return (
        <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4">
            <Head title={title} />
            <div className="max-w-sm text-center">
                <p className="text-6xl font-bold text-[#1F3864] mb-2">{status}</p>
                <h1 className="text-xl font-semibold text-gray-800 mb-2">{title}</h1>
                <p className="text-sm text-gray-500 mb-6">{body}</p>
                <div className="flex items-center justify-center gap-3">
                    <button
                        onClick={() => window.location.reload()}
                        className="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Refresh
                    </button>
                    <Link
                        href="/"
                        className="bg-white border border-[#1F3864] text-[#1F3864] px-4 py-2 rounded-lg text-sm font-semibold"
                    >
                        Go Home
                    </Link>
                </div>
            </div>
        </div>
    );
}
