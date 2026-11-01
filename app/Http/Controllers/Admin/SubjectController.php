<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.subjects.index', [
            'subjects' => Subject::withCount('assignments')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.form', [
            'subject' => new Subject,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $subject = Subject::create(Arr::only($data, ['code', 'name', 'description']));
            $this->assignSchedule($subject, $data);
        });

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        $subject->load(['assignments.classroom', 'assignments.teacher', 'assignments.schedules']);

        return view('admin.subjects.form', [
            'subject' => $subject,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $this->validated($request, $subject);

        DB::transaction(function () use ($subject, $data) {
            $subject->update(Arr::only($data, ['code', 'name', 'description']));
            $this->assignSchedule($subject, $data);
        });

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        DB::transaction(function () use ($subject) {
            $subject->assignments()->each(function (TeachingAssignment $assignment) {
                $assignment->schedules()->delete();
                $assignment->grades()->delete();
                $assignment->delete();
            });

            $subject->delete();
        });

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function destroyAssignment(Subject $subject, TeachingAssignment $assignment): RedirectResponse
    {
        DB::transaction(function () use ($assignment) {
            $assignment->schedules()->delete();
            $assignment->grades()->delete();
            $assignment->delete();
        });

        return redirect()->route('admin.subjects.edit', $subject)
            ->with('success', 'Penugasan mengajar beserta jadwalnya berhasil dihapus.');
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'classroom_id' => ['nullable', 'required_with:teacher_id,day,starts_at,ends_at', 'exists:classrooms,id'],
            'teacher_id' => ['nullable', 'required_with:classroom_id,day,starts_at,ends_at', 'exists:teachers,id'],
            'day' => ['nullable', 'required_with:classroom_id,teacher_id,starts_at,ends_at', 'integer', 'between:1,6'],
            'starts_at' => ['nullable', 'required_with:classroom_id,teacher_id,day,ends_at', 'date_format:H:i'],
            'ends_at' => ['nullable', 'required_with:classroom_id,teacher_id,day,starts_at', 'date_format:H:i', 'after:starts_at'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function formOptions(): array
    {
        return [
            'classrooms' => Classroom::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
        ];
    }

    private function assignSchedule(Subject $subject, array $data): void
    {
        if (blank($data['classroom_id'] ?? null)) {
            return;
        }

        $classroom = Classroom::findOrFail($data['classroom_id']);
        $assignment = TeachingAssignment::firstOrCreate(
            [
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'academic_year' => $classroom->academic_year,
            ],
            ['teacher_id' => $data['teacher_id']]
        );

        if ((int) $assignment->teacher_id !== (int) $data['teacher_id']) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Kelas ini sudah memakai guru lain untuk mata pelajaran yang sama.',
            ]);
        }

        $startsAt = $data['starts_at'].':00';

        if ($assignment->schedules()->where('day', $data['day'])->where('starts_at', $startsAt)->exists()) {
            throw ValidationException::withMessages([
                'starts_at' => 'Jadwal pada hari dan jam tersebut sudah ada.',
            ]);
        }

        $assignment->schedules()->create([
            'day' => $data['day'],
            'starts_at' => $startsAt,
            'ends_at' => $data['ends_at'].':00',
            'room' => $data['room'] ?? null,
        ]);
    }
}
