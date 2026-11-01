<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now();

        $attendanceToday = Attendance::whereDate('date', $today->toDateString())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceRate = Attendance::where('date', '>=', $today->copy()->subDays(30)->toDateString())
            ->selectRaw("coalesce(round(100.0 * sum(case when status = 'hadir' then 1 else 0 end) / nullif(count(*), 0), 1), 0) as rate")
            ->value('rate');

        $staffTotal = Staff::count();
        $staffActive = Staff::where('is_active', true)->count();

        return view('admin.dashboard', [
            'stats' => [
                'students' => Student::where('status', 'aktif')->count(),
                'teachers' => Teacher::count(),
                'classrooms' => Classroom::count(),
                'announcements' => Announcement::where('is_published', true)->count(),
                'attendance_rate' => $attendanceRate ?? 0,
                'staff_total' => $staffTotal,
                'staff_active' => $staffActive,
                'staff_inactive' => $staffTotal - $staffActive,
            ],
            'todaySchedules' => Schedule::with(['assignment.subject', 'assignment.classroom', 'assignment.teacher'])
                ->where('day', $today->isoWeekday())
                ->orderBy('starts_at')
                ->take(8)
                ->get(),
            'attendanceToday' => $attendanceToday,
            'latestAnnouncements' => Announcement::latest('created_at')->take(5)->get(),
            'recentStudents' => Student::with('classroom')->latest()->take(5)->get(),
        ]);
    }
}
