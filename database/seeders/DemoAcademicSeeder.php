<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttachment;
use App\Models\TeachingJournalAttendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoAcademicSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->purge();

            $teachers = $this->seedTeachers();
            $subjects = $this->seedSubjects();
            $classrooms = $this->seedClassrooms($teachers);
            $assignments = $this->seedAssignments($classrooms, $subjects, $teachers);
            $this->seedSchedules($classrooms, $assignments);
            $this->seedStudents($classrooms, $assignments);

            $this->call(TeachingJournalSeeder::class);
        });
    }

    private function purge(): void
    {
        TeachingJournalAttachment::query()->delete();
        TeachingJournalAttendance::query()->delete();
        TeachingJournal::query()->delete();
        Grade::query()->delete();
        Attendance::query()->delete();
        Schedule::query()->delete();
        TeachingAssignment::query()->delete();
        Student::query()->delete();
        Guardian::query()->delete();
        Classroom::query()->delete();
        Subject::query()->delete();
        Teacher::query()->delete();

        User::query()->whereIn('role', [
            User::ROLE_GURU,
            User::ROLE_SISWA,
            User::ROLE_ORANG_TUA,
        ])->delete();
    }

    private function seedTeachers(): array
    {
        $data = [
            ['Ahmad Fauzi, S.Ag.', 'ahmad.fauzi@sekolah.id', 'L', '197911052005011004', '0813-1000-0001', 'guru-01.jpg'],
            ['Dewi Anggraini, S.H.', 'dewi.anggraini@sekolah.id', 'P', '198406122009012002', '0813-1000-0002', 'guru-06.jpg'],
            ['Budi Santoso, S.Pd.', 'guru@sekolah.id', 'L', '197805122006041001', '0813-1000-0003', 'guru-02.jpg'],
            ['Sri Wahyuni, S.Pd.', 'sri.wahyuni@sekolah.id', 'P', '198203172008012003', '0813-1000-0004', 'guru-07.jpg'],
            ['Michael Tanuwijaya, S.Pd.', 'michael.tanuwijaya@sekolah.id', 'L', '198702232011011005', '0813-1000-0005', 'guru-03.jpg'],
            ['Ratna Dewi, S.Si.', 'ratna.dewi@sekolah.id', 'P', '198609212010012005', '0813-1000-0006', 'guru-08.jpg'],
            ['Rudi Hartono, S.Pd.', 'rudi.hartono@sekolah.id', 'L', '198101302007011006', '0813-1000-0007', 'guru-04.jpg'],
            ['Maya Sari, S.Si.', 'maya.sari@sekolah.id', 'P', '198805142012012007', '0813-1000-0008', 'guru-09.jpg'],
            ['Agus Setiawan, S.Pd.', 'agus.setiawan@sekolah.id', 'L', '197608172003121002', '0813-1000-0009', 'guru-05.jpg'],
            ['Intan Permata, S.Kom.', 'intan.permata@sekolah.id', 'P', '199003052015042008', '0813-1000-0010', 'guru-10.jpg'],
        ];

        $teachers = [];
        foreach ($data as [$name, $email, $gender, $nip, $phone, $photo]) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => 'guru123',
                'role' => User::ROLE_GURU,
                'phone' => $phone,
                'is_active' => true,
            ]);

            $teachers[] = Teacher::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'name' => $name,
                'photo_path' => $this->copyPhoto($photo),
                'gender' => $gender,
                'phone' => $phone,
            ]);
        }

        return $teachers;
    }

    private function seedSubjects(): array
    {
        $data = [
            ['PABP', 'Pendidikan Agama dan Budi Pekerti', 'Nilai keagamaan, akhlak mulia, dan toleransi.'],
            ['PPKN', 'Pendidikan Pancasila', 'Pancasila, konstitusi, dan kewarganegaraan.'],
            ['BIN', 'Bahasa Indonesia', 'Literasi, teks argumentasi, dan karya ilmiah.'],
            ['MTK', 'Matematika', 'Aljabar, geometri, statistika, dan kalkulus dasar.'],
            ['BIG', 'Bahasa Inggris', 'Listening, speaking, reading, dan writing.'],
            ['FIS', 'Fisika', 'Mekanika, gelombang, listrik, dan magnet.'],
            ['KIM', 'Kimia', 'Struktur atom, ikatan, larutan, dan reaksi.'],
            ['BIO', 'Biologi', 'Sel, genetika, ekosistem, dan bioteknologi.'],
            ['SEJ', 'Sejarah', 'Sejarah Indonesia dan dunia.'],
            ['INF', 'Informatika', 'Berpikir komputasional, pemrograman, dan literasi digital.'],
        ];

        return array_map(
            fn (array $row) => Subject::create(['code' => $row[0], 'name' => $row[1], 'description' => $row[2]]),
            $data
        );
    }

    private function seedClassrooms(array $teachers): array
    {
        $data = [
            ['X-1', 10],
            ['X-2', 10],
            ['XI-1', 11],
            ['XI-2', 11],
            ['XII-1', 12],
        ];

        $classrooms = [];
        foreach ($data as $index => [$name, $level]) {
            $classrooms[] = Classroom::create([
                'name' => $name,
                'grade_level' => $level,
                'academic_year' => '2026/2027',
                'homeroom_teacher_id' => $teachers[$index]->id,
            ]);
        }

        return $classrooms;
    }

    private function seedAssignments(array $classrooms, array $subjects, array $teachers): array
    {
        $assignments = [];
        foreach ($classrooms as $classroom) {
            foreach ($subjects as $index => $subject) {
                $assignments[] = TeachingAssignment::create([
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers[$index]->id,
                    'academic_year' => $classroom->academic_year,
                ]);
            }
        }

        return $assignments;
    }

    private function seedSchedules(array $classrooms, array $assignments): void
    {
        $slots = [
            ['07:00:00', '08:30:00'],
            ['08:45:00', '10:15:00'],
            ['10:30:00', '12:00:00'],
            ['13:00:00', '14:30:00'],
        ];

        $byClass = collect($assignments)->groupBy('classroom_id');
        foreach ($classrooms as $classroom) {
            $list = $byClass[$classroom->id]->values();
            $pointer = 0;
            foreach (range(1, 5) as $day) {
                foreach (array_slice($slots, 0, $day === 5 ? 2 : 4) as [$start, $end]) {
                    Schedule::create([
                        'teaching_assignment_id' => $list[$pointer % $list->count()]->id,
                        'day' => $day,
                        'starts_at' => $start,
                        'ends_at' => $end,
                        'room' => $classroom->name,
                    ]);
                    $pointer++;
                }
            }
        }
    }

    private function seedStudents(array $classrooms, array $assignments): void
    {
        $male = ['Aditya', 'Bagas', 'Dimas', 'Fajar', 'Gilang', 'Hendra', 'Ilham', 'Joko', 'Kevin', 'Lutfi', 'Naufal', 'Raka', 'Satria', 'Taufik', 'Yoga'];
        $female = ['Aisyah', 'Bella', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Indah', 'Kartika', 'Maya', 'Nadia', 'Putri', 'Rani', 'Salsa', 'Zahra'];
        $family = ['Pratama', 'Saputra', 'Lestari', 'Wijaya', 'Ramadhan', 'Puspita', 'Nugroho', 'Handayani', 'Kusuma', 'Santoso'];
        $parent = ['Andi', 'Bambang', 'Dedi', 'Eko', 'Hadi', 'Imam', 'Joko', 'Kurnia', 'Lukman', 'Maman'];

        $parentUser = User::create([
            'name' => 'Andi Pratama',
            'email' => 'ortu@sekolah.id',
            'password' => 'ortu123',
            'role' => User::ROLE_ORANG_TUA,
            'phone' => '0812-9000-1122',
            'is_active' => true,
        ]);
        $primaryGuardian = Guardian::create([
            'user_id' => $parentUser->id,
            'name' => 'Andi Pratama',
            'phone' => '0812-9000-1122',
            'occupation' => 'Karyawan Swasta',
            'address' => 'Jl. Melati No. 12, Bandung',
        ]);

        $byClass = collect($assignments)->groupBy('classroom_id');
        $types = ['tugas', 'uts', 'uas'];
        $statuses = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'sakit', 'izin', 'hadir', 'hadir', 'hadir', 'hadir', 'alpa'];
        $schoolDays = collect(range(1, 30))
            ->map(fn ($ago) => now()->subDays($ago))
            ->filter(fn ($date) => $date->isWeekday())
            ->take(20)
            ->values();

        $serial = 0;
        foreach ($classrooms as $classIndex => $classroom) {
            $classAssignments = $byClass[$classroom->id]->take(5);

            for ($i = 0; $i < 30; $i++) {
                $serial++;
                $isFemale = $i >= 15;
                $gender = $isFemale ? 'P' : 'L';
                $given = $isFemale ? $female[$i - 15] : $male[$i];
                $surname = $family[($i + $classIndex * 2) % count($family)];
                $name = $given.' '.$surname;
                $photoIndex = (($i % 15) + $classIndex * 3) % 15 + 1;
                $photo = $isFemale
                    ? sprintf('siswa-p-%02d.jpg', $photoIndex)
                    : sprintf('siswa-l-%02d.jpg', $photoIndex);

                $guardian = ($classIndex === 0 && in_array($i, [0, 16], true))
                    ? $primaryGuardian
                    : Guardian::create([
                        'name' => ($isFemale ? 'Ibu ' : 'Bapak ').$parent[($i + $classIndex) % count($parent)].' '.$surname,
                        'phone' => '0813-8000-'.str_pad((string) (1000 + $serial), 4, '0', STR_PAD_LEFT),
                        'occupation' => ['Petani', 'Pedagang', 'Guru', 'Perawat', 'PNS'][$i % 5],
                        'address' => 'Jl. Kenanga No. '.$serial.', Bandung',
                    ]);

                $userId = null;
                if ($serial <= 3) {
                    $userId = User::create([
                        'name' => $name,
                        'email' => $serial === 1 ? 'siswa@sekolah.id' : 'siswa'.$serial.'@sekolah.id',
                        'password' => 'siswa123',
                        'role' => User::ROLE_SISWA,
                        'is_active' => true,
                    ])->id;
                }

                $student = Student::create([
                    'user_id' => $userId,
                    'nis' => '2627'.str_pad((string) $serial, 4, '0', STR_PAD_LEFT),
                    'nisn' => '00'.str_pad((string) (60000 + $serial), 8, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'photo_path' => $this->copyPhoto($photo),
                    'gender' => $gender,
                    'birth_place' => 'Bandung',
                    'birth_date' => now()->subYears(15 + $classroom->grade_level - 10)->subDays($serial * 7)->toDateString(),
                    'classroom_id' => $classroom->id,
                    'guardian_id' => $guardian->id,
                    'address' => 'Jl. Kenanga No. '.$serial.', Bandung',
                    'status' => 'aktif',
                    'entry_year' => 2026 - ($classroom->grade_level - 10),
                ]);

                foreach ($classAssignments as $assignment) {
                    foreach ($types as $typeIndex => $type) {
                        Grade::create([
                            'student_id' => $student->id,
                            'teaching_assignment_id' => $assignment->id,
                            'type' => $type,
                            'score' => rand(700, 980) / 10,
                            'note' => $type === 'tugas' ? 'Penilaian harian' : null,
                            'recorded_at' => now()->subDays(10 + $typeIndex)->toDateString(),
                        ]);
                    }
                }

                foreach ($schoolDays as $day) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'date' => $day->toDateString(),
                        'status' => $statuses[($serial + $day->day) % count($statuses)],
                        'note' => null,
                    ]);
                }
            }
        }
    }

    private function copyPhoto(string $file): ?string
    {
        $source = database_path('seeders/photos/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $target = 'photos/demo/'.$file;

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
