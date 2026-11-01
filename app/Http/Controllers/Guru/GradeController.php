<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $assignments = TeachingAssignment::with(['classroom', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->sortBy(fn ($item) => $item->classroom->name.$item->subject->name)
            ->values();

        $selected = $assignments->firstWhere('id', (int) $request->query('assignment'))
            ?? $assignments->first();

        $students = collect();
        $grades = collect();
        $search = trim((string) $request->query('q'));
        $sort = $request->query('sort') === 'jenis' ? 'jenis' : 'terbaru';

        if ($selected) {
            $students = Student::where('classroom_id', $selected->classroom_id)
                ->where('status', 'aktif')
                ->orderBy('name')
                ->get();

            $grades = Grade::with('student')
                ->where('teaching_assignment_id', $selected->id)
                ->when($search !== '', fn ($query) => $query->whereHas('student', fn ($student) => $student->whereLike('name', "%{$search}%", caseSensitive: false)))
                ->when($sort === 'jenis', fn ($query) => $query
                    ->orderByRaw("case type when 'tugas' then 1 when 'uts' then 2 when 'uas' then 3 when 'proyek' then 4 else 5 end")
                    ->orderByDesc('recorded_at'))
                ->when($sort === 'terbaru', fn ($query) => $query->latest('recorded_at'))
                ->get();
        }

        return view('guru.grades', [
            'assignments' => $assignments,
            'selected' => $selected,
            'students' => $students,
            'grades' => $grades,
            'search' => $search,
            'sort' => $sort,
            'types' => ['tugas' => 'Tugas', 'uts' => 'UTS', 'uas' => 'UAS', 'proyek' => 'Proyek'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);

        $data = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'in:tugas,uts,uas,proyek'],
            'score' => ['required', 'numeric', 'between:0,100'],
            'note' => ['nullable', 'string', 'max:100'],
            'recorded_at' => ['required', 'date'],
        ]);

        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)
            ->whereKey($data['teaching_assignment_id'])
            ->firstOrFail();

        $student = Student::findOrFail($data['student_id']);
        abort_unless($student->classroom_id === $assignment->classroom_id, 403);

        Grade::create([...$data, 'created_by' => auth()->id()]);

        return redirect()
            ->route('guru.grades.index', ['assignment' => $assignment->id])
            ->with('success', "Nilai {$student->name} berhasil disimpan.");
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);
        abort_unless($grade->assignment?->teacher_id === $teacher->id, 403);

        $assignmentId = $grade->teaching_assignment_id;
        $grade->delete();

        return redirect()
            ->route('guru.grades.index', ['assignment' => $assignmentId])
            ->with('success', 'Nilai berhasil dihapus.');
    }
}
