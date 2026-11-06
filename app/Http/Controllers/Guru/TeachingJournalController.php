<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttachment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    private const MAX_ATTACHMENTS = 6;

    public function index(Request $request): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $assignments = TeachingAssignment::with(['classroom', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->sortBy(fn ($item) => $item->classroom->name.$item->subject->name)
            ->values();

        $selected = $assignments->firstWhere('id', (int) $request->query('assignment'));

        $month = $this->month($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $journals = TeachingJournal::with(['teachingAssignment.classroom', 'teachingAssignment.subject', 'attendances', 'attachments'])
            ->forTeacher($teacher->id)
            ->when($selected, fn ($query) => $query->where('teaching_assignment_id', $selected->id))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('guru.journals.index', [
            'assignments' => $assignments,
            'selected' => $selected,
            'month' => $month,
            'journals' => $journals,
            'total' => $journals->total(),
        ]);
    }

    public function create(Request $request): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $date = $this->selectedDate($request->query('date'));

        $schedules = Schedule::with(['assignment.classroom', 'assignment.subject'])
            ->whereHas('assignment', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->where('day', Carbon::parse($date)->isoWeekday())
            ->orderBy('starts_at')
            ->get();

        $existing = TeachingJournal::whereIn('schedule_id', $schedules->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('schedule_id');

        $schedule = null;
        $students = collect();
        $attendances = collect();

        if ($request->filled('schedule')) {
            $schedule = $schedules->firstWhere('id', (int) $request->query('schedule'));
            abort_if(! $schedule, 404);

            if ($journal = $existing->get($schedule->id)) {
                return redirect()
                    ->route('guru.journals.edit', $journal)
                    ->with('info', 'Jurnal untuk sesi ini sudah diisi.');
            }

            $students = $this->studentsFor($schedule);
        }

        return view('guru.journals.form', [
            'mode' => 'create',
            'schedule' => $schedule,
            'schedules' => $schedules,
            'existing' => $existing,
            'students' => $students,
            'attendances' => $attendances,
            'journal' => null,
            'date' => $date,
            'statuses' => Attendance::STATUSES,
            'maxAttachments' => self::MAX_ATTACHMENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);

        $data = $this->validateJournal($request);

        $schedule = Schedule::with('assignment')->findOrFail($data['schedule_id']);
        $assignment = $schedule->assignment;
        abort_unless($assignment && $assignment->teacher_id === $teacher->id, 403);

        $date = Carbon::parse($data['date']);

        if ($date->isAfter(today())) {
            throw ValidationException::withMessages(['date' => 'Tanggal jurnal tidak boleh melebihi hari ini.']);
        }

        if ((int) $schedule->day !== $date->isoWeekday()) {
            throw ValidationException::withMessages(['schedule_id' => 'Sesi tidak sesuai dengan hari pada tanggal yang dipilih.']);
        }

        if (TeachingJournal::where('schedule_id', $schedule->id)->whereDate('date', $date->toDateString())->exists()) {
            throw ValidationException::withMessages(['schedule_id' => 'Jurnal untuk sesi dan tanggal ini sudah ada.']);
        }

        $students = $this->studentsFor($schedule);

        $journal = DB::transaction(function () use ($data, $schedule, $assignment, $teacher, $students, $request) {
            $journal = TeachingJournal::create([
                'teaching_assignment_id' => $assignment->id,
                'schedule_id' => $schedule->id,
                'date' => $data['date'],
                'starts_at' => $schedule->starts_at,
                'ends_at' => $schedule->ends_at,
                'topic' => $data['topic'],
                'activity' => $data['activity'],
                'assignment' => $data['assignment'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncAttendance($journal, $students, $data['statuses'] ?? []);
            $this->storeAttachments($journal, $request->file('attachments', []));

            return $journal;
        });

        return redirect()
            ->route('guru.journals.edit', $journal)
            ->with('success', 'Jurnal mengajar berhasil disimpan.');
    }

    public function edit(TeachingJournal $journal): View
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);
        abort_unless($journal->teachingAssignment?->teacher_id === $teacher->id, 403);

        $journal->load(['teachingAssignment.classroom', 'teachingAssignment.subject', 'attendances', 'attachments']);

        return view('guru.journals.form', [
            'mode' => 'edit',
            'schedule' => $journal->schedule,
            'schedules' => collect(),
            'existing' => collect(),
            'students' => $this->studentsFor($journal->schedule ?? $journal->teachingAssignment),
            'attendances' => $journal->attendances->pluck('status', 'student_id'),
            'journal' => $journal,
            'date' => $journal->date->toDateString(),
            'statuses' => Attendance::STATUSES,
            'maxAttachments' => self::MAX_ATTACHMENTS,
        ]);
    }

    public function update(Request $request, TeachingJournal $journal): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);
        abort_unless($journal->teachingAssignment?->teacher_id === $teacher->id, 403);

        $data = $this->validateJournal($request, false);
        $journal->load(['teachingAssignment', 'schedule', 'attendances', 'attachments']);

        $students = $this->studentsFor($journal->schedule ?? $journal->teachingAssignment);

        DB::transaction(function () use ($journal, $data, $students, $request) {
            $journal->update([
                'topic' => $data['topic'],
                'activity' => $data['activity'],
                'assignment' => $data['assignment'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncAttendance($journal, $students, $data['statuses'] ?? []);
            $this->storeAttachments($journal, $request->file('attachments', []));
        });

        return redirect()
            ->route('guru.journals.edit', $journal)
            ->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingJournal $journal): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);
        abort_unless($journal->teachingAssignment?->teacher_id === $teacher->id, 403);

        $journal->load('attachments');
        Storage::disk('public')->delete($journal->attachments->pluck('path')->all());
        $journal->delete();

        return redirect()
            ->route('guru.journals.index')
            ->with('success', 'Jurnal mengajar berhasil dihapus.');
    }

    public function destroyAttachment(TeachingJournal $journal, TeachingJournalAttachment $attachment): RedirectResponse
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403);
        abort_unless($journal->teachingAssignment?->teacher_id === $teacher->id, 403);
        abort_unless($attachment->journal_id === $journal->id, 404);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return redirect()
            ->route('guru.journals.edit', $journal)
            ->with('success', 'Lampiran berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJournal(Request $request, bool $creating = true): array
    {
        $rules = [
            'topic' => ['required', 'string', 'max:150'],
            'activity' => ['required', 'string', 'max:5000'],
            'assignment' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['required', 'in:hadir,sakit,izin,alpa'],
            'attachments' => ['nullable', 'array', 'max:'.self::MAX_ATTACHMENTS],
            'attachments.*' => ['file', 'max:4096', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];

        if ($creating) {
            $rules['date'] = ['required', 'date', 'before_or_equal:today'];
            $rules['schedule_id'] = ['required', 'integer', 'exists:schedules,id'];
        }

        return $request->validate($rules, [
            'date.before_or_equal' => 'Tanggal jurnal tidak boleh melebihi hari ini.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 4 MB.',
            'attachments.*.mimes' => 'Lampiran harus berupa gambar JPG/PNG/WebP atau dokumen PDF.',
        ]);
    }

    private function studentsFor(Schedule|TeachingAssignment|null $source)
    {
        $assignment = $source instanceof Schedule ? $source->assignment : $source;
        abort_if(! $assignment, 404);

        return Student::where('classroom_id', $assignment->classroom_id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();
    }

    private function syncAttendance(TeachingJournal $journal, $students, array $statuses): void
    {
        foreach ($students as $student) {
            $journal->attendances()->updateOrCreate(
                ['student_id' => $student->id],
                ['status' => $statuses[$student->id] ?? 'hadir']
            );
        }
    }

    private function storeAttachments(TeachingJournal $journal, array $files): void
    {
        if ($files === []) {
            return;
        }

        $current = $journal->attachments()->count();

        foreach (array_slice($files, 0, max(0, self::MAX_ATTACHMENTS - $current)) as $file) {
            $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf';

            $journal->attachments()->create([
                'type' => $isPdf ? TeachingJournalAttachment::DOCUMENT : TeachingJournalAttachment::PHOTO,
                'path' => $file->store($isPdf ? 'documents/jurnal' : 'photos/jurnal', 'public'),
                'original_name' => substr($file->getClientOriginalName(), 0, 150),
            ]);
        }
    }

    private function month(mixed $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        }

        return now()->startOfMonth();
    }

    private function selectedDate(mixed $value): string
    {
        if (! is_string($value) || ! strtotime($value)) {
            return now()->toDateString();
        }

        $date = Carbon::parse($value)->startOfDay();

        return $date->isAfter(today()) ? now()->toDateString() : $date->toDateString();
    }
}
