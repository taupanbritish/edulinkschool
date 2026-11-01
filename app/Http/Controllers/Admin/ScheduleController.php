<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $schedules = Schedule::with(['assignment.classroom', 'assignment.subject', 'assignment.teacher'])
            ->when($request->query('classroom'), fn ($query, $id) => $query->whereHas('assignment', fn ($sub) => $sub->where('classroom_id', $id)))
            ->when($request->query('day'), fn ($query, $day) => $query->where('day', $day))
            ->orderBy('day')
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.schedules.form', [
            'schedule' => new Schedule([
                'day' => 1,
                'teaching_assignment_id' => $request->integer('assignment') ?: null,
            ]),
            'assignments' => $this->assignmentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Schedule::create($this->validated($request));

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        return view('admin.schedules.form', [
            'schedule' => $schedule,
            'assignments' => $this->assignmentOptions(),
        ]);
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($this->validated($request, $schedule));

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    private function assignmentOptions()
    {
        return TeachingAssignment::with(['classroom', 'subject', 'teacher'])
            ->get()
            ->sortBy(fn ($item) => $item->classroom->name.' '.$item->subject->name)
            ->values();
    }

    private function validated(Request $request, ?Schedule $schedule = null): array
    {
        return $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day' => ['required', 'integer', 'between:1,6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
