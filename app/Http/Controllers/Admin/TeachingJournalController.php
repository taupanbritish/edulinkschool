<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Support\ReportWriter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $journals = $this->query($filters)
            ->orderByDesc('date')
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.journals.index', [
            'journals' => $journals,
            'teachers' => Teacher::orderBy('name')->get(['id', 'name']),
            'classrooms' => Classroom::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'total' => $journals->total(),
        ]);
    }

    public function show(TeachingJournal $journal): View
    {
        $journal->load([
            'teachingAssignment.classroom',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'attendances.student',
            'attachments',
            'author',
        ]);

        return view('admin.journals.show', [
            'journal' => $journal,
            'attendances' => $journal->attendances->sortBy(fn ($attendance) => $attendance->student?->name)->values(),
            'counts' => $journal->attendanceCounts(),
        ]);
    }

    public function export(Request $request, string $format): Response
    {
        $filters = $this->filters($request);
        $journals = $this->query($filters)->orderBy('date')->orderBy('starts_at')->get();

        $meta = [
            'Periode' => $filters['month']->locale('id')->translatedFormat('F Y'),
            'Guru' => $filters['teacher_id'] ? (Teacher::find($filters['teacher_id'])?->name ?? 'Semua guru') : 'Semua guru',
            'Kelas' => $filters['classroom_id'] ? (Classroom::find($filters['classroom_id'])?->name ?? 'Semua kelas') : 'Semua kelas',
            'Jumlah jurnal' => $journals->count(),
            'Dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];

        $rows = $journals->values()->map(function (TeachingJournal $journal, int $index) {
            $counts = $journal->attendanceCounts();

            return [
                'no' => $index + 1,
                'date' => $journal->date->locale('id')->translatedFormat('d/m/Y'),
                'day' => $journal->dayName(),
                'time' => $journal->timeRange(),
                'classroom' => $journal->teachingAssignment->classroom->name,
                'subject' => $journal->teachingAssignment->subject->name,
                'teacher' => $journal->teachingAssignment->teacher->name,
                'topic' => $journal->topic,
                'activity' => $journal->activity,
                'assignment' => $journal->assignment ?: '-',
                'notes' => $journal->notes ?: '-',
                'hadir' => $counts['hadir'],
                'sakit' => $counts['sakit'],
                'izin' => $counts['izin'],
                'alpa' => $counts['alpa'],
                'attachments' => $journal->attachments->count(),
            ];
        })->all();

        $filename = 'jurnal-mengajar-'.$filters['month']->format('Y-m');

        if ($format === 'pdf') {
            return ReportWriter::pdf('admin.journals.report-pdf', [
                'title' => 'Rekap Jurnal Mengajar',
                'meta' => $meta,
                'rows' => $rows,
                'signatureRole' => auth()->user()->roleLabel(),
                'signatureName' => auth()->user()->name,
            ], $filename, 'landscape');
        }

        $headers = ['No', 'Tanggal', 'Hari', 'Waktu', 'Kelas', 'Mata Pelajaran', 'Guru', 'Materi', 'Kegiatan', 'Tugas', 'Catatan', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Lampiran'];
        $data = array_map(fn (array $row) => [
            $row['no'],
            $row['date'],
            $row['day'],
            $row['time'],
            $row['classroom'],
            $row['subject'],
            $row['teacher'],
            $row['topic'],
            $row['activity'],
            $row['assignment'],
            $row['notes'],
            $row['hadir'],
            $row['sakit'],
            $row['izin'],
            $row['alpa'],
            $row['attachments'],
        ], $rows);

        $sheet = ReportWriter::spreadsheet('Jurnal Mengajar', $meta, $headers, $data, [5, 11, 9, 13, 10, 16, 22, 26, 40, 26, 26, 7, 7, 7, 7, 9]);

        return ReportWriter::xlsx($sheet, $filename);
    }

    /**
     * @return array{teacher_id: int|null, classroom_id: int|null, month: Carbon}
     */
    private function filters(Request $request): array
    {
        $value = $request->query('month');
        $month = is_string($value) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)
            ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()
            : now()->startOfMonth();

        return [
            'teacher_id' => $request->integer('teacher') ?: null,
            'classroom_id' => $request->integer('classroom') ?: null,
            'month' => $month,
        ];
    }

    /**
     * @param  array{teacher_id: int|null, classroom_id: int|null, month: Carbon}  $filters
     */
    private function query(array $filters): Builder
    {
        $start = $filters['month']->copy()->startOfMonth();
        $end = $filters['month']->copy()->endOfMonth();

        return TeachingJournal::query()
            ->with([
                'teachingAssignment.classroom',
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
                'attendances',
                'attachments',
            ])
            ->when($filters['teacher_id'], fn (Builder $query, int $id) => $query
                ->whereHas('teachingAssignment', fn (Builder $assignment) => $assignment->where('teacher_id', $id)))
            ->when($filters['classroom_id'], fn (Builder $query, int $id) => $query
                ->whereHas('teachingAssignment', fn (Builder $assignment) => $assignment->where('classroom_id', $id)))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
    }
}
