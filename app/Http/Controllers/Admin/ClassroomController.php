<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        return view('admin.classrooms.index', [
            'classrooms' => Classroom::with('homeroomTeacher')->withCount('students', 'assignments')->orderBy('name')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.classrooms.form', [
            'classroom' => new Classroom(['grade_level' => 10, 'academic_year' => '2026/2027']),
            'teachers' => Teacher::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Classroom::create($this->validated($request));

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('admin.classrooms.form', [
            'classroom' => $classroom,
            'teachers' => Teacher::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($this->validated($request, $classroom));

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        if ($classroom->students()->exists()) {
            return back()->with('error', 'Kelas masih memiliki siswa. Pindahkan siswa terlebih dahulu.');
        }

        if ($classroom->assignments()->exists()) {
            return back()->with('error', 'Kelas masih memiliki penugasan mengajar dan jadwal.');
        }

        $classroom->delete();

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }

    private function validated(Request $request, ?Classroom $classroom = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('classrooms')->where('academic_year', $request->input('academic_year'))->ignore($classroom?->id)],
            'grade_level' => ['required', 'integer', 'in:10,11,12'],
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);
    }
}
