<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeachingJournalResource;
use App\Models\TeachingJournal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeachingJournalController extends Controller
{
    use ResolvesStudent;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2020,2100'],
        ], [
            'month.integer' => 'Bulan tidak valid.',
            'month.between' => 'Bulan tidak valid.',
            'year.integer' => 'Tahun tidak valid.',
            'year.between' => 'Tahun tidak valid.',
        ]);

        $student = $this->resolveStudent($request);
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $journals = $this->queryFor($student->classroom_id, $student->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->orderByDesc('starts_at')
            ->get();

        $statusCounts = $journals
            ->map(fn (TeachingJournal $journal) => $journal->attendances->first()?->status)
            ->filter()
            ->countBy();

        return response()->json([
            'siswa' => new StudentResource($student),
            'periode' => [
                'bulan' => $month,
                'tahun' => $year,
                'label' => now()->setDate($year, $month, 1)->locale('id')->translatedFormat('F Y'),
            ],
            'ringkasan' => [
                'total_jurnal' => $journals->count(),
                'hadir' => $statusCounts->get('hadir', 0),
                'sakit' => $statusCounts->get('sakit', 0),
                'izin' => $statusCounts->get('izin', 0),
                'alpa' => $statusCounts->get('alpa', 0),
            ],
            'jurnal' => TeachingJournalResource::collection($journals),
        ]);
    }

    public function show(Request $request, TeachingJournal $journal): JsonResponse
    {
        $student = $this->resolveStudent($request);

        abort_unless(
            $journal->teachingAssignment?->classroom_id === $student->classroom_id,
            403,
            'Anda tidak memiliki akses ke jurnal kelas tersebut.'
        );

        $journal->load([
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.classroom',
            'schedule',
            'attachments',
            'attendances' => fn ($query) => $query->where('student_id', $student->id),
        ]);

        return response()->json([
            'siswa' => new StudentResource($student),
            'jurnal' => new TeachingJournalResource($journal),
        ]);
    }

    private function queryFor(?int $classroomId, int $studentId): Builder
    {
        return TeachingJournal::query()
            ->with([
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
                'teachingAssignment.classroom',
                'schedule',
                'attachments',
                'attendances' => fn ($query) => $query->where('student_id', $studentId),
            ])
            ->whereHas('teachingAssignment', fn (Builder $assignment) => $assignment->where('classroom_id', $classroomId));
    }
}
