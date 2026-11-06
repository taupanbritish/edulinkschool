<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Support\ReportWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    private const TYPES = ['tugas' => 'Tugas', 'uts' => 'UTS', 'uas' => 'UAS', 'proyek' => 'Proyek'];

    public function grades(Request $request, string $format): Response
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $assignment = TeachingAssignment::with(['classroom', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($request->integer('assignment'));

        $search = trim((string) $request->query('q'));

        $students = Student::where('classroom_id', $assignment->classroom_id)
            ->where('status', 'aktif')
            ->when($search !== '', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
            ->orderBy('name')
            ->get();

        $grades = Grade::where('teaching_assignment_id', $assignment->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $rows = $students->values()->map(function (Student $student, int $index) use ($grades) {
            $studentGrades = $grades->get($student->id, collect());
            $scores = [];
            $average = null;

            foreach (self::TYPES as $type => $label) {
                $averageScore = $studentGrades->where('type', $type)->avg('score');
                $scores[$type] = $averageScore === null ? null : round($averageScore, 1);
            }

            if ($studentGrades->isNotEmpty()) {
                $average = round($studentGrades->avg('score'), 1);
            }

            return [
                'no' => $index + 1,
                'name' => $student->name,
                'nis' => $student->nis,
                'scores' => $scores,
                'average' => $average,
                'notes' => $studentGrades->pluck('note')->filter()->unique()->implode('; '),
            ];
        })->all();

        $meta = [
            'Kelas' => $assignment->classroom->name.' ('.$assignment->academic_year.')',
            'Mata pelajaran' => $assignment->subject->name.' ('.$assignment->subject->code.')',
            'Guru' => $teacher->name,
            'Dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];

        $filename = 'nilai-'.Str::slug($assignment->classroom->name.'-'.$assignment->subject->name);

        if ($format === 'pdf') {
            return ReportWriter::pdf('guru.reports.grades-pdf', [
                'title' => 'Laporan Daftar Nilai',
                'meta' => $meta,
                'rows' => $rows,
                'types' => self::TYPES,
                'signatureRole' => 'Guru Mata Pelajaran',
                'signatureName' => $teacher->name,
            ], $filename);
        }

        $headers = ['No', 'Nama Siswa', 'NIS', ...array_values(self::TYPES), 'Rata-rata', 'Catatan'];
        $data = array_map(fn (array $row) => [
            $row['no'],
            $row['name'],
            $row['nis'],
            ...array_map(fn (string $type) => $row['scores'][$type], array_keys(self::TYPES)),
            $row['average'],
            $row['notes'],
        ], $rows);

        $sheet = ReportWriter::spreadsheet('Daftar Nilai', $meta, $headers, $data, [5, 32, 14, 10, 10, 10, 10, 12, 40]);

        return ReportWriter::xlsx($sheet, $filename);
    }

    public function attendance(Request $request, string $format): Response
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $classroom = Classroom::where('homeroom_teacher_id', $teacher->id)
            ->find($request->integer('classroom'));

        abort_if(! $classroom, 403, 'Hanya wali kelas yang dapat mengunduh laporan absensi kelas ini.');

        $request->validate(['date' => ['nullable', 'date']]);
        $date = $request->query('date');
        $month = $date ? Carbon::parse($date) : now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $students = Student::where('classroom_id', $classroom->id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $records = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('student_id');

        $totals = array_fill_keys(array_keys(Attendance::STATUSES), 0);

        $rows = $students->values()->map(function (Student $student, int $index) use ($records, &$totals) {
            $studentRecords = $records->get($student->id, collect());
            $counts = [];

            foreach (array_keys(Attendance::STATUSES) as $status) {
                $counts[$status] = $studentRecords->where('status', $status)->count();
                $totals[$status] += $counts[$status];
            }

            $total = $studentRecords->count();

            return [
                'no' => $index + 1,
                'name' => $student->name,
                'nis' => $student->nis,
                'counts' => $counts,
                'total' => $total,
                'percentage' => $total > 0 ? round($counts['hadir'] / $total * 100, 1) : null,
            ];
        })->all();

        $meta = [
            'Kelas' => $classroom->name.' ('.$classroom->academic_year.')',
            'Wali kelas' => $teacher->name,
            'Periode' => $start->locale('id')->translatedFormat('F Y'),
            'Dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];

        $filename = 'absensi-'.Str::slug($classroom->name.'-'.$start->format('Y-m'));

        if ($format === 'pdf') {
            return ReportWriter::pdf('guru.reports.attendance-pdf', [
                'title' => 'Laporan Absensi Bulanan',
                'meta' => $meta,
                'rows' => $rows,
                'totals' => $totals,
                'statuses' => Attendance::STATUSES,
                'signatureRole' => 'Wali Kelas',
                'signatureName' => $teacher->name,
            ], $filename);
        }

        $headers = ['No', 'Nama Siswa', 'NIS', ...array_values(Attendance::STATUSES), 'Total', '% Hadir'];
        $data = array_map(fn (array $row) => [
            $row['no'],
            $row['name'],
            $row['nis'],
            ...array_values($row['counts']),
            $row['total'],
            $row['percentage'],
        ], $rows);

        $data[] = ['', 'Jumlah', '', ...array_values($totals), array_sum($totals), null];

        $sheet = ReportWriter::spreadsheet('Absensi', $meta, $headers, $data, [5, 32, 14, 9, 9, 9, 9, 10, 12]);

        return ReportWriter::xlsx($sheet, $filename);
    }
}
