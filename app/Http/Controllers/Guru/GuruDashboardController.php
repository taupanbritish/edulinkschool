<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    public function __invoke(): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $assignments = TeachingAssignment::with(['classroom', 'subject'])
            ->withCount('grades')
            ->where('teacher_id', $teacher->id)
            ->get();

        $classroomIds = $assignments->pluck('classroom_id')->unique();
        $studentCount = Student::whereIn('classroom_id', $classroomIds)->where('status', 'aktif')->count();

        return view('guru.dashboard', [
            'teacher' => $teacher,
            'assignments' => $assignments,
            'homeroomClassrooms' => $teacher->classrooms()->orderBy('name')->get(),
            'todaySchedules' => Schedule::with(['assignment.classroom', 'assignment.subject'])
                ->whereHas('assignment', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->where('day', now()->isoWeekday())
                ->orderBy('starts_at')
                ->get(),
            'studentCount' => $studentCount,
            'gradeCount' => Grade::whereIn('teaching_assignment_id', $assignments->pluck('id'))->count(),
            'recentGrades' => Grade::with(['student', 'assignment.subject', 'assignment.classroom'])
                ->whereIn('teaching_assignment_id', $assignments->pluck('id'))
                ->latest('recorded_at')
                ->take(6)
                ->get(),
        ]);
    }
}
