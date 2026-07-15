<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $teacher = $request->user();

        return Inertia::render('Dashboard', [
            'classes' => $teacher->classesTaught()->withCount('students')->get(),
            'exams' => $teacher->examsCreated()->latest()->take(5)->get(),
        ]);
    }
}
