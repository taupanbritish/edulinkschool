<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeachingJournalResource;
use App\Http\Resources\UserResource;
use App\Models\Announcement;
use App\Models\TeachingJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ResolvesStudent;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $this->resolveStudent($request);

        $counts = $student->attendances()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAttendance = (int) $counts->sum();
        $hadir = (int) ($counts['hadir'] ?? 0);

        return response()->json([
            'user' => new UserResource($user),
            'siswa' => new StudentResource($student),
            'anak' => StudentResource::collection($this->linkedStudents($user)),
            'hari_ini' => now()->locale('id')->translatedFormat('l'),
            'tanggal' => now()->locale('id')->translatedFormat('j F Y'),
            'ringkasan' => [
                'rata_nilai' => round((float) $student->grades()->avg('score'), 1),
                'jumlah_mapel' => $student->grades()->distinct()->count('teaching_assignment_id'),
                'jumlah_nilai' => $student->grades()->count(),
                'kehadiran_persen' => $totalAttendance > 0 ? round($hadir / $totalAttendance * 100, 1) : 0,
                'total_absensi' => $totalAttendance,
                'hadir' => $hadir,
                'sakit' => (int) ($counts['sakit'] ?? 0),
                'izin' => (int) ($counts['izin'] ?? 0),
                'alpa' => (int) ($counts['alpa'] ?? 0),
            ],
            'jadwal_hari_ini' => ScheduleResource::collection(
                $student->schedules()
                    ->where('day', now()->isoWeekday())
                    ->orderBy('starts_at')
                    ->get()
            ),
            'nilai_terbaru' => GradeResource::collection(
                $student->grades()
                    ->with(['assignment.subject', 'assignment.teacher'])
                    ->latest('recorded_at')
                    ->latest('id')
                    ->take(6)
                    ->get()
            ),
            'absensi_terbaru' => AttendanceResource::collection(
                $student->attendances()
                    ->latest('date')
                    ->take(5)
                    ->get()
            ),
            'jurnal_terbaru' => TeachingJournalResource::collection(
                TeachingJournal::forClassroom($student->classroom_id)
                    ->with([
                        'teachingAssignment.subject',
                        'teachingAssignment.teacher',
                        'teachingAssignment.classroom',
                        'schedule',
                        'attachments',
                        'attendances' => fn ($query) => $query->where('student_id', $student->id),
                    ])
                    ->orderByDesc('date')
                    ->orderByDesc('starts_at')
                    ->take(4)
                    ->get()
            ),
            'pengumuman_terbaru' => AnnouncementResource::collection(
                Announcement::published()
                    ->with('author')
                    ->latest('published_at')
                    ->take(3)
                    ->get()
            ),
        ]);
    }
}
