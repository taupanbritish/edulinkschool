<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\StudentResource;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use ResolvesStudent;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'day' => ['nullable', 'integer', 'between:1,6'],
        ], [
            'day.between' => 'Hari tidak valid.',
        ]);

        $student = $this->resolveStudent($request);
        $schedules = $student->schedules()
            ->orderBy('day')
            ->orderBy('starts_at')
            ->get();

        $today = now()->isoWeekday();
        $selected = (int) $request->input('day', $today > 6 ? 1 : $today);

        return response()->json([
            'siswa' => new StudentResource($student),
            'hari_ini' => $today,
            'hari_terpilih' => $selected,
            'hari' => collect(range(1, 6))->map(fn (int $day) => [
                'hari' => $day,
                'nama' => Schedule::DAYS[$day],
                'ini_hari_ini' => $day === $today,
                'jadwal' => ScheduleResource::collection($schedules->where('day', $day)->values()),
            ])->values(),
        ]);
    }
}
