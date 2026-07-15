<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Sprint 2: Class Management (QES-14 to QES-19).
 */
class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Classes/Index', [
            'classes' => $request->user()->classesTaught()
                ->withCount('students')
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Classes/Create');
    }

    public function store(Request $request) // FR-2.1
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
        ]);

        $class = $request->user()->classesTaught()->create($data); // join_code auto-generated (FR-2.2)

        return redirect()->route('classes.show', $class)->with('success', 'Class created.');
    }

    public function show(SchoolClass $schoolClass)
    {
        $this->authorizeOwnership($schoolClass);

        return Inertia::render('Classes/Show', [
            'class' => $schoolClass->load('students'),
        ]);
    }

    public function edit(SchoolClass $schoolClass)
    {
        $this->authorizeOwnership($schoolClass);

        return Inertia::render('Classes/Edit', ['class' => $schoolClass]);
    }

    public function update(Request $request, SchoolClass $schoolClass) // FR-2.1 (edit)
    {
        $this->authorizeOwnership($schoolClass);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
        ]);

        $schoolClass->update($data);

        return back()->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $schoolClass) // FR-2.5
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }

    public function removeStudent(SchoolClass $schoolClass, \App\Models\User $student) // FR-2.4
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->enrollments()->where('student_id', $student->id)->delete();

        return back()->with('success', 'Student removed from class.');
    }

    public function archive(SchoolClass $schoolClass) // FR-2.5
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->update(['is_archived' => true]);

        return back()->with('success', 'Class archived.');
    }

    protected function authorizeOwnership(SchoolClass $schoolClass): void
    {
        abort_unless($schoolClass->teacher_id === auth()->id(), 403);
    }
}
