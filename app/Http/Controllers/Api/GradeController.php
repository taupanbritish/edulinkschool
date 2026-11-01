<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    use ResolvesStudent;

    public function index(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        $grades = $student->grades()
            ->with(['assignment.subject', 'assignment.teacher'])
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();

        $subjects = $grades
            ->groupBy('teaching_assignment_id')
            ->map(function ($items) {
                $assignment = $items->first()->assignment;

                return [
                    'mapel' => $assignment?->subject ? [
                        'id' => $assignment->subject->id,
                        'kode' => $assignment->subject->code,
                        'nama' => $assignment->subject->name,
                    ] : null,
                    'guru' => $assignment?->teacher?->name,
                    'guru_foto' => $assignment?->teacher?->photoUrl(),
                    'rata_rata' => round((float) $items->avg('score'), 1),
                    'jumlah' => $items->count(),
                    'nilai' => GradeResource::collection($items->values()),
                ];
            })
            ->sortBy(fn ($item) => $item['mapel']['nama'] ?? '')
            ->values();

        return response()->json([
            'siswa' => new StudentResource($student),
            'rata_rata' => round((float) $grades->avg('score'), 1),
            'total' => $grades->count(),
            'mapel' => $subjects,
        ]);
    }
}
