<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttachment;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class TeachingJournalTest extends TestCase
{
    use RefreshDatabase;

    private const MONTH = '2026-10';

    private const DAY = '2026-11-02';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-02 09:00:00');
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function login(string $email, string $password = 'guru123'): void
    {
        $this->post('/login', ['email' => $email, 'password' => $password])->assertRedirect();
    }

    private function teacher(): Teacher
    {
        return Teacher::whereHas('user', fn ($query) => $query->where('email', 'guru@sekolah.id'))->firstOrFail();
    }

    private function ownSchedule(int $day = 1): Schedule
    {
        $teacher = $this->teacher();

        return Schedule::with(['assignment.classroom', 'assignment.teacher'])
            ->where('day', $day)
            ->whereHas('assignment', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->orderBy('starts_at')
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Schedule $schedule, string $date = self::DAY): array
    {
        $student = Student::where('classroom_id', $schedule->assignment->classroom_id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->firstOrFail();

        return [
            'date' => $date,
            'schedule_id' => $schedule->id,
            'topic' => 'Topik jurnal pengujian',
            'activity' => 'Siswa berdiskusi dan menyelesaikan latihan terbimbing.',
            'assignment' => 'Latihan halaman 20 nomor 1–5.',
            'notes' => 'Kelas berjalan kondusif.',
            'statuses' => [$student->id => 'sakit'],
        ];
    }

    public function test_teacher_can_create_journal_with_attendance_and_attachments(): void
    {
        Storage::fake('public');
        $this->login('guru@sekolah.id');

        $schedule = $this->ownSchedule();
        $students = Student::where('classroom_id', $schedule->assignment->classroom_id)
            ->where('status', 'aktif')
            ->get();

        $response = $this->post(route('guru.journals.store'), array_merge($this->payload($schedule), [
            'attachments' => [
                UploadedFile::fake()->create('dokumentasi.jpg', 120, 'image/jpeg'),
                UploadedFile::fake()->create('materi.pdf', 80, 'application/pdf'),
            ],
        ]));

        $journal = TeachingJournal::where('schedule_id', $schedule->id)
            ->whereDate('date', self::DAY)
            ->firstOrFail();

        $response->assertRedirect(route('guru.journals.edit', $journal));
        $response->assertSessionHas('success');
        $this->assertSame('Topik jurnal pengujian', $journal->topic);
        $this->assertSame($students->count(), $journal->attendances()->count());

        $student = $students->firstWhere('id', array_key_first($this->payload($schedule)['statuses']));
        $this->assertDatabaseHas('teaching_journal_attendances', [
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => 'sakit',
        ]);

        $this->assertSame(2, $journal->attachments()->count());
        $photo = $journal->attachments()->where('type', TeachingJournalAttachment::PHOTO)->firstOrFail();
        $document = $journal->attachments()->where('type', TeachingJournalAttachment::DOCUMENT)->firstOrFail();
        $this->assertStringStartsWith('photos/jurnal/', $photo->path);
        $this->assertStringStartsWith('documents/jurnal/', $document->path);
        Storage::disk('public')->assertExists($photo->path);
        Storage::disk('public')->assertExists($document->path);
    }

    public function test_journal_rejects_duplicate_future_and_mismatched_sessions(): void
    {
        $this->login('guru@sekolah.id');
        $schedule = $this->ownSchedule();

        $this->post(route('guru.journals.store'), $this->payload($schedule))->assertRedirect();

        $this->from(route('guru.journals.create', ['date' => self::DAY, 'schedule' => $schedule->id]))
            ->post(route('guru.journals.store'), $this->payload($schedule))
            ->assertSessionHasErrors('schedule_id');

        $this->assertSame(1, TeachingJournal::where('schedule_id', $schedule->id)
            ->whereDate('date', self::DAY)
            ->count());

        $this->from(route('guru.journals.create'))
            ->post(route('guru.journals.store'), $this->payload($schedule, '2026-11-03'))
            ->assertSessionHasErrors('date');

        $otherDay = $this->ownSchedule(2);

        $this->from(route('guru.journals.create'))
            ->post(route('guru.journals.store'), $this->payload($otherDay, self::DAY))
            ->assertSessionHasErrors('schedule_id');

        $this->assertSame(0, TeachingJournal::where('schedule_id', $otherDay->id)
            ->whereDate('date', self::DAY)
            ->count());
    }

    public function test_teacher_cannot_manage_another_teachers_journal(): void
    {
        $this->login('guru@sekolah.id');
        $teacher = $this->teacher();

        $foreignJournal = TeachingJournal::whereHas(
            'teachingAssignment',
            fn ($query) => $query->where('teacher_id', '!=', $teacher->id)
        )->firstOrFail();

        $this->put(route('guru.journals.update', $foreignJournal), [
            'topic' => 'Percobaan perubahan',
            'activity' => 'Tidak boleh tersimpan.',
        ])->assertForbidden();

        $this->delete(route('guru.journals.destroy', $foreignJournal))->assertForbidden();

        $otherSchedule = Schedule::with('assignment')
            ->where('day', 1)
            ->whereHas('assignment', fn ($query) => $query->where('teacher_id', '!=', $teacher->id))
            ->firstOrFail();

        $this->post(route('guru.journals.store'), [
            'date' => self::DAY,
            'schedule_id' => $otherSchedule->id,
            'topic' => 'Sesi guru lain',
            'activity' => 'Tidak boleh tersimpan.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('teaching_journals', [
            'schedule_id' => $otherSchedule->id,
            'date' => self::DAY,
        ]);
    }

    public function test_teacher_can_update_journal_and_remove_attachments(): void
    {
        Storage::fake('public');
        $this->login('guru@sekolah.id');

        $schedule = $this->ownSchedule();
        $student = Student::where('classroom_id', $schedule->assignment->classroom_id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->firstOrFail();

        $journal = TeachingJournal::create([
            'teaching_assignment_id' => $schedule->teaching_assignment_id,
            'schedule_id' => $schedule->id,
            'date' => self::DAY,
            'starts_at' => $schedule->starts_at,
            'ends_at' => $schedule->ends_at,
            'topic' => 'Topik awal',
            'activity' => 'Kegiatan awal.',
            'created_by' => $schedule->assignment->teacher->user_id,
        ]);

        $journal->attendances()->create(['student_id' => $student->id, 'status' => 'hadir']);

        Storage::disk('public')->put('photos/jurnal/lama.jpg', 'demo');
        $attachment = $journal->attachments()->create([
            'type' => TeachingJournalAttachment::PHOTO,
            'path' => 'photos/jurnal/lama.jpg',
            'original_name' => 'lama.jpg',
        ]);

        $this->put(route('guru.journals.update', $journal), [
            'topic' => 'Topik revisi',
            'activity' => 'Kegiatan revisi.',
            'assignment' => 'Tugas revisi.',
            'notes' => 'Catatan revisi.',
            'statuses' => [$student->id => 'izin'],
        ])->assertRedirect(route('guru.journals.edit', $journal));

        $this->assertDatabaseHas('teaching_journals', [
            'id' => $journal->id,
            'topic' => 'Topik revisi',
        ]);
        $this->assertDatabaseHas('teaching_journal_attendances', [
            'journal_id' => $journal->id,
            'student_id' => $student->id,
            'status' => 'izin',
        ]);

        $this->delete(route('guru.journals.attachments.destroy', [$journal, $attachment]))
            ->assertRedirect(route('guru.journals.edit', $journal));

        $this->assertDatabaseMissing('teaching_journal_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing('photos/jurnal/lama.jpg');

        $this->delete(route('guru.journals.destroy', $journal))
            ->assertRedirect(route('guru.journals.index'));

        $this->assertDatabaseMissing('teaching_journals', ['id' => $journal->id]);
        $this->assertDatabaseMissing('teaching_journal_attendances', ['journal_id' => $journal->id]);
    }

    public function test_teacher_journal_pages_render(): void
    {
        $this->login('guru@sekolah.id');
        $teacher = $this->teacher();
        $schedule = $this->ownSchedule();

        $journal = TeachingJournal::whereHas(
            'teachingAssignment',
            fn ($query) => $query->where('teacher_id', $teacher->id)
        )->firstOrFail();

        $this->get(route('guru.journals.index', ['month' => self::MONTH]))
            ->assertOk()
            ->assertSee('Jurnal Mengajar');

        $this->get(route('guru.journals.create', ['date' => self::DAY]))
            ->assertOk()
            ->assertSee($schedule->assignment->subject->name);

        $this->get(route('guru.journals.create', ['date' => self::DAY, 'schedule' => $schedule->id]))
            ->assertOk()
            ->assertSee('Simpan Jurnal');

        $this->get(route('guru.journals.edit', $journal))
            ->assertOk()
            ->assertSee($journal->topic);
    }

    public function test_teacher_journal_table_is_paginated_per_month(): void
    {
        $this->login('guru@sekolah.id');

        $teacher = $this->teacher();
        $schedule = $this->ownSchedule();

        foreach (range(1, 16) as $index) {
            $label = str_pad((string) $index, 2, '0', STR_PAD_LEFT);

            TeachingJournal::create([
                'teaching_assignment_id' => $schedule->teaching_assignment_id,
                'schedule_id' => $schedule->id,
                'date' => '2026-09-'.$label,
                'starts_at' => $schedule->starts_at,
                'ends_at' => $schedule->ends_at,
                'topic' => 'Materi paginasi '.$label,
                'activity' => 'Kegiatan paginasi '.$label,
                'created_by' => $teacher->user_id,
            ]);
        }

        $this->get(route('guru.journals.index', ['month' => '2026-09']))
            ->assertOk()
            ->assertSee('aria-label="Navigasi halaman"', false)
            ->assertSee('Materi paginasi 16')
            ->assertDontSee('Materi paginasi 01')
            ->assertViewHas('journals', fn ($journals) => $journals->total() === 16
                && $journals->perPage() === 15
                && $journals->count() === 15);

        $this->get(route('guru.journals.index', ['month' => '2026-09', 'page' => 2]))
            ->assertOk()
            ->assertSee('Materi paginasi 01')
            ->assertViewHas('journals', fn ($journals) => $journals->currentPage() === 2
                && $journals->count() === 1);
    }

    public function test_admin_and_headmaster_can_review_and_export_journals(): void
    {
        $this->login('kepsek@sekolah.id', 'kepsek123');

        $journal = TeachingJournal::whereBetween('date', ['2026-10-01', '2026-10-31'])
            ->orderByDesc('date')
            ->firstOrFail();

        $this->get(route('admin.journals.index', ['month' => self::MONTH]))
            ->assertOk()
            ->assertSee('Jurnal Mengajar')
            ->assertSee($journal->topic);

        $this->get(route('admin.journals.show', $journal))
            ->assertOk()
            ->assertSee($journal->topic);

        $pdf = $this->get(route('admin.journals.export', ['format' => 'pdf', 'month' => self::MONTH]));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->get(route('admin.journals.export', ['format' => 'xlsx', 'month' => self::MONTH]));
        $excel->assertOk();
        $this->assertStringContainsString('spreadsheetml', $excel->headers->get('content-type'));

        $sheet = $this->spreadsheetFrom($excel->getContent())->getActiveSheet();
        $this->assertSame('Jurnal Mengajar', $sheet->getTitle());
        $this->assertSame('Tanggal', $sheet->getCell('B8')->getValue());
    }

    public function test_student_api_only_exposes_own_classroom_journals(): void
    {
        $token = $this->tokenFor('siswa@sekolah.id', 'siswa123');
        $headers = ['Authorization' => "Bearer {$token}"];
        $student = Student::whereHas('user', fn ($query) => $query->where('email', 'siswa@sekolah.id'))->firstOrFail();

        $response = $this->withHeaders($headers)->getJson('/api/jurnal?month=10&year=2026');

        $response->assertOk()->assertJsonStructure([
            'siswa' => ['id', 'nama', 'kelas'],
            'periode' => ['bulan', 'tahun', 'label'],
            'ringkasan' => ['total_jurnal', 'hadir', 'sakit', 'izin', 'alpa'],
            'jurnal' => [[
                'id', 'tanggal', 'tanggal_label', 'hari', 'rentang_waktu',
                'mapel' => ['id', 'kode', 'nama'], 'guru', 'kelas',
                'materi', 'kegiatan', 'tugas', 'catatan', 'kehadiran_saya', 'lampiran',
            ]],
        ]);

        $journals = collect($response->json('jurnal'));
        $this->assertNotEmpty($journals);
        $this->assertSame([$student->classroom->name], $journals->pluck('kelas')->unique()->values()->all());

        $journal = $journals->first();
        $this->assertNotNull($journal['kehadiran_saya']);

        $this->withHeaders($headers)
            ->getJson('/api/jurnal/'.$journal['id'])
            ->assertOk()
            ->assertJsonPath('jurnal.id', $journal['id']);

        $foreignJournal = TeachingJournal::whereHas(
            'teachingAssignment',
            fn ($query) => $query->where('classroom_id', '!=', $student->classroom_id)
        )->firstOrFail();

        $this->withHeaders($headers)
            ->getJson('/api/jurnal/'.$foreignJournal->id)
            ->assertForbidden();

        $this->withHeaders($headers)
            ->getJson('/api/jurnal?month=13')
            ->assertStatus(422)
            ->assertJsonValidationErrors('month');
    }

    public function test_parent_api_can_read_journals_per_child(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ortu@sekolah.id',
            'password' => 'ortu123',
        ]);
        $response->assertOk();

        $headers = ['Authorization' => 'Bearer '.$response->json('token')];
        $children = collect($response->json('siswa'));

        $child = $children->last();

        $journals = $this->withHeaders($headers)
            ->getJson('/api/jurnal?student_id='.$child['id'].'&month=10&year=2026')
            ->assertOk()
            ->assertJsonPath('siswa.id', $child['id'])
            ->json('jurnal');

        $this->assertNotEmpty($journals);
        $this->assertSame([$child['kelas']['nama']], collect($journals)->pluck('kelas')->unique()->values()->all());

        $unrelated = Student::whereNotIn('id', $children->pluck('id'))->firstOrFail();

        $this->withHeaders($headers)
            ->getJson('/api/jurnal?student_id='.$unrelated->id.'&month=10&year=2026')
            ->assertForbidden();
    }

    public function test_dashboard_includes_recent_journals(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'siswa@sekolah.id',
            'password' => 'siswa123',
        ]);
        $response->assertOk();

        $headers = ['Authorization' => 'Bearer '.$response->json('token')];

        $this->withHeaders($headers)->getJson('/api/beranda')
            ->assertOk()
            ->assertJsonStructure([
                'jurnal_terbaru' => [[
                    'id', 'tanggal', 'materi', 'kegiatan', 'mapel', 'guru', 'kehadiran_saya',
                ]],
            ])
            ->assertJsonCount(4, 'jurnal_terbaru');
    }

    private function tokenFor(string $email, string $password): string
    {
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
            'device_name' => 'phpunit',
        ]);

        $response->assertOk();

        return $response->json('token');
    }

    private function spreadsheetFrom(string $content): Spreadsheet
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $content);

        try {
            return IOFactory::load($path);
        } finally {
            @unlink($path);
        }
    }
}
