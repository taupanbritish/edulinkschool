<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $classrooms = Classroom::where('homeroom_teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $selected = $classrooms->firstWhere('id', (int) $request->query('classroom'))
            ?? $classrooms->first();

        $date = $request->query('date', now()->toDateString());

        $students = collect();
        $existing = collect();

        if ($selected) {
            $students = Student::where('classroom_id', $selected->id)
                ->where('status', 'aktif')
                ->orderBy('name')
                ->get();

            $existing = Attendance::whereIn('student_id', $students->pluck('id'))
                ->whereDate('date', $date)
                ->pluck('status', 'student_id');
        }

        return view('guru.attendance', [
            'classrooms' => $classrooms,
            'selected' => $selected,
            'students' => $students,
            'existing' => $existing,
            'date' => $date,
            'statuses' => Attendance::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,sakit,izin,alpa'],
        ]);

        $classroom = Classroom::whereKey($data['classroom_id'])
            ->where('homeroom_teacher_id', $teacher->id)
            ->first();

        abort_if(! $classroom, 403, 'Hanya wali kelas yang dapat menginput absensi kelas ini.');

        $studentIds = Student::where('classroom_id', $classroom->id)
            ->where('status', 'aktif')
            ->pluck('id');

        $saved = 0;
        foreach ($data['statuses'] as $studentId => $status) {
            if (! $studentIds->contains((int) $studentId)) {
                continue;
            }

            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $data['date']],
                ['status' => $status, 'recorded_by' => auth()->id()]
            );
            $saved++;
        }

        return redirect()
            ->route('guru.attendance.index', ['classroom' => $classroom->id, 'date' => $data['date']])
            ->with('success', "Absensi {$saved} siswa berhasil disimpan.");
    }
}
