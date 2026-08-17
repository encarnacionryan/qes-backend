<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

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

    public function store(Request $request) 
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
        ]);

        $class = $request->user()->classesTaught()->create($data); 

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

    public function update(Request $request, SchoolClass $schoolClass) 
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

    public function destroy(SchoolClass $schoolClass) 
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }

    public function removeStudent(SchoolClass $schoolClass, \App\Models\User $student)
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->enrollments()->where('student_id', $student->id)->delete();

        return back()->with('success', 'Student removed from class.');
    }

    public function archive(SchoolClass $schoolClass) 
    {
        $this->authorizeOwnership($schoolClass);
        $schoolClass->update(['is_archived' => true]);

        return back()->with('success', 'Class archived.');
    }

    public function importStudents(Request $request, SchoolClass $schoolClass)
    {
        $this->authorizeOwnership($schoolClass);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows) ?? []);

        $nameIdx = array_search('name', $header, true);
        $emailIdx = array_search('email', $header, true);

        if ($nameIdx === false || $emailIdx === false) {
            return back()->with('error', 'CSV must have "name" and "email" columns in the header row.');
        }

        $created = [];
        $enrolled = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2; 
            if (count($row) <= max($nameIdx, $emailIdx) || trim($row[$nameIdx] ?? '') === '') {
                continue; 
            }

            $name = trim($row[$nameIdx]);
            $email = strtolower(trim($row[$emailIdx]));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $rowNumber, 'name' => $name, 'email' => $email, 'reason' => 'Invalid email address'];
                continue;
            }

            $existing = User::where('email', $email)->first();

            if ($existing && $existing->role !== 'student') {
                $errors[] = ['row' => $rowNumber, 'name' => $name, 'email' => $email, 'reason' => 'Email already belongs to a non-student account'];
                continue;
            }

            if ($existing) {
                $alreadyEnrolled = $schoolClass->enrollments()->where('student_id', $existing->id)->exists();
                if ($alreadyEnrolled) {
                    $errors[] = ['row' => $rowNumber, 'name' => $name, 'email' => $email, 'reason' => 'Already enrolled in this class'];
                } else {
                    $schoolClass->enrollments()->create(['student_id' => $existing->id]);
                    $enrolled[] = ['name' => $existing->name, 'email' => $email];
                }
                continue;
            }

            $tempPassword = Str::random(10);
            $student = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($tempPassword),
                'role' => 'student',
            ]);
            $schoolClass->enrollments()->create(['student_id' => $student->id]);
            $created[] = ['name' => $name, 'email' => $email, 'password' => $tempPassword];
        }

        return Inertia::render('Classes/ImportResults', [
            'class' => $schoolClass,
            'created' => $created,
            'enrolled' => $enrolled,
            'errors' => $errors,
        ]);
    }

    protected function authorizeOwnership(SchoolClass $schoolClass): void
    {
        abort_unless($schoolClass->teacher_id === auth()->id(), 403);
    }
}
