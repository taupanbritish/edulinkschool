<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
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

        $query = $student->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        $counts = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $counts->sum();
        $hadir = (int) ($counts['hadir'] ?? 0);

        return response()->json([
            'siswa' => new StudentResource($student),
            'periode' => [
                'bulan' => $month,
                'tahun' => $year,
                'label' => now()->setDate($year, $month, 1)->locale('id')->translatedFormat('F Y'),
            ],
            'ringkasan' => [
                'hadir' => $hadir,
                'sakit' => (int) ($counts['sakit'] ?? 0),
                'izin' => (int) ($counts['izin'] ?? 0),
                'alpa' => (int) ($counts['alpa'] ?? 0),
                'total' => $total,
                'persen_hadir' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ],
            'catatan' => AttendanceResource::collection(
                (clone $query)->latest('date')->get()
            ),
        ]);
    }
}
