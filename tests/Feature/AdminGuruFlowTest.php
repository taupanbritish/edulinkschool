<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class AdminGuruFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function login(string $email, string $password = 'admin123'): void
    {
        $this->post('/login', ['email' => $email, 'password' => $password])
            ->assertRedirect('/dashboard');
    }

    private function teacherUser(): User
    {
        return User::where('email', 'guru@sekolah.id')->firstOrFail();
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

    public function test_admin_creates_student_with_mobile_account(): void
    {
        $this->login('admin@sekolah.id');

        $classroom = Classroom::orderBy('id')->firstOrFail();

        $this->post('/admin/students', [
            'nis' => '26279999',
            'nisn' => '0000599999',
            'name' => 'Siswa Baru',
            'gender' => 'P',
            'classroom_id' => $classroom->id,
            'status' => 'aktif',
            'entry_year' => 2026,
            'email' => 'siswabaru@sekolah.id',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.students.index'));

        $student = Student::where('nis', '26279999')->firstOrFail();

        $this->assertSame('Siswa Baru', $student->name);
        $this->assertSame('P', $student->gender);
        $this->assertNotNull($student->user);
        $this->assertSame('siswa', $student->user->role);

        $this->postJson('/api/login', [
            'email' => 'siswabaru@sekolah.id',
            'password' => 'rahasia123',
        ])->assertOk()->assertJsonPath('siswa.0.nama', 'Siswa Baru');
    }

    public function test_admin_updates_and_deletes_student(): void
    {
        $this->login('admin@sekolah.id');

        $student = Student::orderBy('id')->firstOrFail();

        $this->put('/admin/students/'.$student->id, [
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'name' => 'Nama Diperbarui',
            'gender' => 'P',
            'classroom_id' => $student->classroom_id,
            'status' => 'aktif',
            'entry_year' => $student->entry_year,
        ])->assertRedirect(route('admin.students.index'));

        $this->assertSame('Nama Diperbarui', $student->fresh()->name);

        $this->delete('/admin/students/'.$student->id)
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_teacher_stores_grade_for_own_class_only(): void
    {
        $this->login('guru@sekolah.id', 'guru123');

        $teacher = $this->teacherUser()->teacher;
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();
        $student = Student::where('classroom_id', $assignment->classroom_id)->firstOrFail();

        $this->post('/guru/nilai', [
            'teaching_assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'type' => 'uts',
            'score' => 91.5,
            'recorded_at' => now()->toDateString(),
        ])->assertRedirect(route('guru.grades.index', ['assignment' => $assignment->id]));

        $this->assertDatabaseHas('grades', [
            'student_id' => $student->id,
            'teaching_assignment_id' => $assignment->id,
            'type' => 'uts',
            'created_by' => $this->teacherUser()->id,
        ]);

        $otherAssignment = TeachingAssignment::where('teacher_id', '!=', $teacher->id)->firstOrFail();
        $otherStudent = Student::where('classroom_id', $otherAssignment->classroom_id)->firstOrFail();

        $this->post('/guru/nilai', [
            'teaching_assignment_id' => $otherAssignment->id,
            'student_id' => $otherStudent->id,
            'type' => 'uts',
            'score' => 80,
            'recorded_at' => now()->toDateString(),
        ])->assertNotFound();
    }

    public function test_teacher_saves_attendance_and_it_reaches_mobile_api(): void
    {
        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'guru@sekolah.id'))->firstOrFail();
        $classroom = Classroom::where('homeroom_teacher_id', $teacher->id)->firstOrFail();
        $students = Student::where('classroom_id', $classroom->id)
            ->orderBy('id')
            ->take(2)
            ->get();

        $this->login('guru@sekolah.id', 'guru123');

        $this->post('/guru/absensi', [
            'classroom_id' => $classroom->id,
            'date' => now()->toDateString(),
            'statuses' => [
                $students[0]->id => 'hadir',
                $students[1]->id => 'sakit',
            ],
        ])->assertRedirect(route('guru.attendance.index', [
            'classroom' => $classroom->id,
            'date' => now()->toDateString(),
        ]));

        $this->assertSame(2, Attendance::whereDate('date', now()->toDateString())
            ->whereIn('student_id', $students->pluck('id'))
            ->count());

        $studentUser = User::where('email', 'siswa@sekolah.id')->firstOrFail();

        $this->post('/logout');
        $this->app['auth']->forgetGuards();

        $token = $this->postJson('/api/login', [
            'email' => $studentUser->email,
            'password' => 'siswa123',
        ])->json('token');

        $this->withToken($token)
            ->getJson('/api/absensi?month='.now()->month.'&year='.now()->year)
            ->assertOk()
            ->assertJsonPath('siswa.id', $studentUser->student->id);
    }

    public function test_grade_history_supports_search_sort_and_note_column(): void
    {
        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'guru@sekolah.id'))->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();
        $students = Student::where('classroom_id', $assignment->classroom_id)
            ->orderBy('name')
            ->take(2)
            ->get();

        Grade::where('teaching_assignment_id', $assignment->id)->delete();

        Grade::create([
            'teaching_assignment_id' => $assignment->id,
            'student_id' => $students[0]->id,
            'type' => 'uts',
            'score' => 88,
            'note' => 'Catatan UTS',
            'recorded_at' => '2026-10-01',
        ]);
        Grade::create([
            'teaching_assignment_id' => $assignment->id,
            'student_id' => $students[1]->id,
            'type' => 'tugas',
            'score' => 92,
            'note' => 'Catatan Tugas',
            'recorded_at' => '2026-10-02',
        ]);

        $this->login('guru@sekolah.id', 'guru123');

        $this->get(route('guru.grades.index', [
            'assignment' => $assignment->id,
            'q' => $students[0]->name,
        ]))
            ->assertOk()
            ->assertSee('Catatan')
            ->assertSee('Catatan UTS')
            ->assertViewHas('grades', fn ($grades) => $grades->total() === 1
                && $grades->items()[0]->student_id === $students[0]->id);

        $this->get(route('guru.grades.index', [
            'assignment' => $assignment->id,
            'sort' => 'jenis',
        ]))
            ->assertOk()
            ->assertViewHas('grades', fn ($grades) => collect($grades->items())->pluck('type')->all() === ['tugas', 'uts']);
    }

    public function test_teacher_grade_history_is_paginated_and_keeps_filters(): void
    {
        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'guru@sekolah.id'))->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();
        $student = Student::where('classroom_id', $assignment->classroom_id)->firstOrFail();
        $total = Grade::where('teaching_assignment_id', $assignment->id)->count() + 18;

        foreach (range(1, 18) as $index) {
            Grade::create([
                'teaching_assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'type' => 'tugas',
                'score' => 70 + $index,
                'recorded_at' => '2026-10-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $this->login('guru@sekolah.id', 'guru123');

        $this->get(route('guru.grades.index', ['assignment' => $assignment->id, 'sort' => 'terbaru']))
            ->assertOk()
            ->assertSee('aria-label="Navigasi halaman"', false)
            ->assertViewHas('grades', fn ($grades) => $grades->perPage() === 15
                && $grades->count() === 15
                && $grades->total() === $total);

        $this->get(route('guru.grades.index', ['assignment' => $assignment->id, 'sort' => 'terbaru', 'page' => 2]))
            ->assertOk()
            ->assertSee('aria-current="page">2<', false)
            ->assertViewHas('grades', fn ($grades) => $grades->currentPage() === 2
                && $grades->count() === min(15, $total - 15));
    }

    public function test_teacher_exports_grade_and_attendance_reports_as_pdf_and_excel(): void
    {
        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'guru@sekolah.id'))->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();
        $classroom = Classroom::where('homeroom_teacher_id', $teacher->id)->firstOrFail();

        $this->login('guru@sekolah.id', 'guru123');

        $gradePdf = $this->get(route('guru.grades.export', ['format' => 'pdf', 'assignment' => $assignment->id]));
        $gradePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $gradePdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $gradePdf->getContent());

        $gradeExcel = $this->get(route('guru.grades.export', ['format' => 'xlsx', 'assignment' => $assignment->id]));
        $gradeExcel->assertOk();
        $this->assertStringContainsString('spreadsheetml', $gradeExcel->headers->get('content-type'));
        $sheet = $this->spreadsheetFrom($gradeExcel->getContent())->getActiveSheet();
        $this->assertSame('Daftar Nilai', $sheet->getTitle());
        $this->assertSame('Nama Siswa', $sheet->getCell('B7')->getValue());

        $attendancePdf = $this->get(route('guru.attendance.export', ['format' => 'pdf', 'classroom' => $classroom->id]));
        $attendancePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $attendancePdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $attendancePdf->getContent());

        $attendanceExcel = $this->get(route('guru.attendance.export', ['format' => 'xlsx', 'classroom' => $classroom->id]));
        $attendanceExcel->assertOk();
        $this->assertStringContainsString('spreadsheetml', $attendanceExcel->headers->get('content-type'));
        $sheet = $this->spreadsheetFrom($attendanceExcel->getContent())->getActiveSheet();
        $this->assertSame('Absensi', $sheet->getTitle());
        $this->assertSame('Nama Siswa', $sheet->getCell('B7')->getValue());
    }

    public function test_only_homeroom_teachers_can_input_attendance(): void
    {
        $homeroomTeacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'guru@sekolah.id'))->firstOrFail();
        $classroom = Classroom::where('homeroom_teacher_id', $homeroomTeacher->id)->firstOrFail();
        $student = Student::where('classroom_id', $classroom->id)->firstOrFail();
        $nonHomeroom = Teacher::whereHas('user', fn ($q) => $q->where('email', 'ratna.dewi@sekolah.id'))->firstOrFail();

        $this->assertFalse(Classroom::where('homeroom_teacher_id', $nonHomeroom->id)->exists());

        $this->login('ratna.dewi@sekolah.id', 'guru123');

        $this->get(route('guru.dashboard'))
            ->assertOk()
            ->assertDontSee('Isi Absensi');

        $this->get(route('guru.attendance.index'))
            ->assertOk()
            ->assertSee('Anda belum ditetapkan sebagai wali kelas')
            ->assertDontSee('Simpan Absensi');

        $this->post('/guru/absensi', [
            'classroom_id' => $classroom->id,
            'date' => now()->toDateString(),
            'statuses' => [$student->id => 'alpa'],
        ])->assertForbidden();

        $this->assertDatabaseMissing('attendances', [
            'student_id' => $student->id,
            'date' => now()->toDateString(),
        ]);

        $this->get(route('guru.attendance.export', ['format' => 'pdf', 'classroom' => $classroom->id]))
            ->assertForbidden();
        $this->get(route('guru.attendance.export', ['format' => 'xlsx', 'classroom' => $classroom->id]))
            ->assertForbidden();

        $foreignAssignment = TeachingAssignment::where('teacher_id', $homeroomTeacher->id)->firstOrFail();
        $this->get(route('guru.grades.export', ['format' => 'pdf', 'assignment' => $foreignAssignment->id]))
            ->assertNotFound();
    }
}
