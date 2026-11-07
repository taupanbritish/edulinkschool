<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\OrganizationMember;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedSettings();
            $this->seedAdmin();
            $teachers = $this->seedTeachers();
            [$classrooms, $subjects, $assignments] = $this->seedAcademic($teachers);
            $this->seedStudents($classrooms, $assignments);
            $this->seedStaffAndOrganization();
            $this->call(AnnouncementGallerySeeder::class);
            $this->call(ShowcaseSeeder::class);
            $this->call(AcademicContentSeeder::class);
            $this->call(StudentLifeSeeder::class);
            $this->call(PortalContentSeeder::class);
            $this->call(ContactMessageSeeder::class);
            $this->call(TeachingJournalSeeder::class);
        });
    }

    private function seedSettings(): void
    {
        $settings = [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'tagline' => 'Berprestasi, Berkarakter, Berdaya Saing',
            'hero_title' => 'Belajar Bermakna, Tumbuh *Berkarakter*',
            'hero_subtitle' => 'SMA Edulink School mendampingi siswa meraih prestasi melalui pembelajaran bermakna, lingkungan aman, dan kemitraan erat bersama orang tua.',
            'npsn' => '20219876',
            'accreditation' => 'A',
            'founded_year' => '1985',
            'address' => 'Jl. Pendidikan Raya No. 1, Kec. Cibeunying Kaler, Kota Bandung 40123',
            'phone' => '(022) 720-1985',
            'whatsapp' => '0812-2000-1985',
            'email' => 'info@edulinkschool.sch.id',
            'office_hours' => 'Senin – Jumat, 07.00 – 15.00 WIB',
            'principal_name' => 'Dra. Siti Rahmawati, M.Pd.',
            'principal_welcome' => 'Assalamualaikum warahmatullahi wabarakatuh. Selamat datang di SMA Edulink School. Kami berkomitmen membangun generasi yang berprestasi, berkarakter, dan berdaya saing melalui pembelajaran yang bermakna, lingkungan yang aman, serta kemitraan erat dengan orang tua dan masyarakat.',
            'vision' => 'Terwujudnya peserta didik yang beriman, berprestasi, berkarakter Pancasila, dan siap bersaing di era global.',
            'mission' => "Menyelenggarakan pembelajaran aktif, inovatif, dan menyenangkan.\nMenumbuhkan budaya disiplin, jujur, dan saling menghormati.\nMengembangkan potensi akademik dan non-akademik peserta didik.\nMembangun kemitraan dengan orang tua, alumni, dan dunia usaha.",
            'history' => 'SMA Edulink School berdiri pada tahun 1985 di Kota Bandung sebagai sekolah menengah atas yang tumbuh bersama masyarakat. Selama empat dekade, sekolah ini telah meluluskan lebih dari 12.000 alumni yang berkontribusi di berbagai bidang. Sejak 2016, sekolah menerapkan Kurikulum Merdeka dengan penguatan projek profil pelajar Pancasila.',
            'curriculum_name' => 'Kurikulum Merdeka',
            'curriculum_description' => "SMA Edulink School menerapkan Kurikulum Merdeka dengan pembelajaran intrakurikuler yang beragam, projek penguatan profil pelajar Pancasila, dan fleksibilitas bagi siswa untuk memilih mata pelajaran sesuai minat, bakat, dan rencana studi lanjut.\n\nPendekatan pembelajaran berpusat pada siswa melalui diskusi, projek, praktik laboratorium, dan asesmen formatif. Guru berperan sebagai fasilitator yang mendampingi setiap siswa mencapai capaian pembelajaran.",
            'maps_url' => 'https://maps.google.com/?q=SMA+Edulink+School',
            'youtube_url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::set('principal_photo_path', $this->copyPhoto('kepala-sekolah.jpg'));
    }

    private function seedAdmin(): User
    {
        $accounts = [
            ['Administrator Sekolah', 'admin@sekolah.id', 'admin123', User::ROLE_ADMIN, '0812-3456-7890'],
            ['Petugas Web Sekolah', 'petugas@sekolah.id', 'petugas123', User::ROLE_PETUGAS, '0812-3456-7891'],
            ['Dra. Siti Rahmawati, M.Pd.', 'kepsek@sekolah.id', 'kepsek123', User::ROLE_KEPALA_SEKOLAH, '0812-3456-7892'],
            ['Drs. Agus Priyanto, M.Pd.', 'wakasek@sekolah.id', 'wakasek123', User::ROLE_WAKASEK_KURIKULUM, '0812-3456-7893'],
        ];

        foreach ($accounts as [$name, $email, $password, $role, $phone]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => $phone,
                'is_active' => true,
            ]);
        }

        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    private function seedTeachers(): array
    {
        $data = [
            ['Budi Santoso, S.Pd.', 'guru@sekolah.id', 'L', '197805122006041001', '0813-1111-2222'],
            ['Sri Wahyuni, S.Pd.', 'sri.wahyuni@sekolah.id', 'P', '198203172008012003', '0813-2222-3333'],
            ['Ahmad Fauzi, M.Pd.', 'ahmad.fauzi@sekolah.id', 'L', '197911052005011004', '0813-3333-4444'],
            ['Ratna Dewi, S.Si.', 'ratna.dewi@sekolah.id', 'P', '198609212010012005', '0813-4444-5555'],
        ];

        $teachers = [];
        foreach ($data as $index => [$name, $email, $gender, $nip, $phone]) {
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
                'photo_path' => match ($name) {
                    'Budi Santoso, S.Pd.' => $this->copyPhoto('guru-budi.jpg'),
                    'Sri Wahyuni, S.Pd.' => $this->copyPhoto('guru-sri.jpg'),
                    default => null,
                },
                'gender' => $gender,
                'phone' => $phone,
            ]);
        }

        return $teachers;
    }

    private function seedAcademic(array $teachers): array
    {
        $classrooms = [
            Classroom::create(['name' => 'X-1', 'grade_level' => 10, 'academic_year' => '2026/2027', 'homeroom_teacher_id' => $teachers[0]->id]),
            Classroom::create(['name' => 'X-2', 'grade_level' => 10, 'academic_year' => '2026/2027', 'homeroom_teacher_id' => $teachers[1]->id]),
            Classroom::create(['name' => 'XI-1', 'grade_level' => 11, 'academic_year' => '2026/2027', 'homeroom_teacher_id' => $teachers[2]->id]),
        ];

        $subjects = [
            Subject::create(['code' => 'MTK', 'name' => 'Matematika', 'description' => 'Aljabar, geometri, statistika, dan kalkulus dasar.']),
            Subject::create(['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'description' => 'Literasi, teks argumentasi, dan karya ilmiah.']),
            Subject::create(['code' => 'BIG', 'name' => 'Bahasa Inggris', 'description' => 'Listening, speaking, reading, dan writing.']),
            Subject::create(['code' => 'FIS', 'name' => 'Fisika', 'description' => 'Mekanika, gelombang, listrik, dan magnet.']),
            Subject::create(['code' => 'KIM', 'name' => 'Kimia', 'description' => 'Struktur atom, ikatan, larutan, dan reaksi.']),
            Subject::create(['code' => 'BIO', 'name' => 'Biologi', 'description' => 'Sel, genetika, ekosistem, dan bioteknologi.']),
            Subject::create(['code' => 'SEJ', 'name' => 'Sejarah', 'description' => 'Sejarah Indonesia dan dunia.']),
            Subject::create(['code' => 'PJOK', 'name' => 'PJOK', 'description' => 'Pendidikan jasmani, olahraga, dan kesehatan.']),
        ];

        $assignments = [];
        foreach ($classrooms as $classroom) {
            foreach ($subjects as $i => $subject) {
                $assignments[] = TeachingAssignment::create([
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers[$i % count($teachers)]->id,
                    'academic_year' => $classroom->academic_year,
                ]);
            }
        }

        $slots = [
            ['07:00:00', '08:30:00'],
            ['08:45:00', '10:15:00'],
            ['10:30:00', '12:00:00'],
            ['13:00:00', '14:30:00'],
        ];

        $classAssignments = collect($assignments)->groupBy('classroom_id');
        foreach ($classrooms as $classroom) {
            $list = $classAssignments[$classroom->id]->values();
            $pointer = 0;
            foreach (range(1, 5) as $day) {
                foreach (array_slice($slots, 0, $day === 5 ? 2 : 4) as [$start, $end]) {
                    $assignment = $list[$pointer % $list->count()];
                    Schedule::create([
                        'teaching_assignment_id' => $assignment->id,
                        'day' => $day,
                        'starts_at' => $start,
                        'ends_at' => $end,
                        'room' => $classroom->name,
                    ]);
                    $pointer++;
                }
            }
        }

        return [$classrooms, $subjects, $assignments];
    }

    private function seedStudents(array $classrooms, array $assignments): void
    {
        $given = ['Ananda', 'Bagas', 'Citra', 'Dimas', 'Eka', 'Fajar', 'Gita', 'Hendra', 'Indah', 'Joko', 'Kartika', 'Lutfi', 'Maya', 'Nanda', 'Oka', 'Putri', 'Rizky', 'Salsa', 'Taufik', 'Umi', 'Vino', 'Wulan', 'Yoga', 'Zahra', 'Arif', 'Bella', 'Candra', 'Dewi', 'Edo', 'Fitri'];
        $family = ['Pratama', 'Saputra', 'Lestari', 'Wijaya', 'Ramadhan', 'Puspita', 'Nugroho', 'Handayani'];
        $parentGiven = ['Andi', 'Bambang', 'Dedi', 'Eko', 'Hadi', 'Imam', 'Joko', 'Kurnia', 'Lukman', 'Maman'];
        $femaleNames = [3, 7, 9, 11, 13, 16, 18, 20, 22, 24, 26, 28, 30];

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

        $studentUser = User::create([
            'name' => 'Ananda Pratama',
            'email' => 'siswa@sekolah.id',
            'password' => 'siswa123',
            'role' => User::ROLE_SISWA,
            'phone' => '0812-9000-3344',
            'is_active' => true,
        ]);

        $guardians = [];
        $students = [];

        foreach ($given as $i => $name) {
            $gender = in_array($i + 1, $femaleNames, true) ? 'P' : 'L';
            $classroom = $classrooms[intdiv($i, 10)];

            if ($i === 0 || $i === 16) {
                $guardian = $primaryGuardian;
            } else {
                $guardian = Guardian::create([
                    'name' => ($gender === 'P' ? 'Bapak ' : 'Bapak ').$parentGiven[$i % count($parentGiven)].' '.$family[$i % count($family)],
                    'phone' => '0813-8000-'.str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
                    'occupation' => ['Petani', 'Pedagang', 'Guru', 'Perawat', 'PNS'][$i % 5],
                    'address' => 'Jl. Kenanga No. '.($i + 1).', Bandung',
                ]);
            }
            $guardians[] = $guardian;

            $students[] = Student::create([
                'user_id' => $i === 0 ? $studentUser->id : ($i === 1 ? $this->createStudentUser($name, 'siswa2@sekolah.id') : ($i === 2 ? $this->createStudentUser($name, 'siswa3@sekolah.id') : null)),
                'nis' => '2627'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'nisn' => '00'.str_pad((string) (50000 + $i), 8, '0', STR_PAD_LEFT),
                'name' => $name.' '.$family[$i % count($family)],
                'photo_path' => match ($i) {
                    0 => $this->copyPhoto('siswa-ananda.jpg'),
                    16 => $this->copyPhoto('siswa-rizky.jpg'),
                    default => null,
                },
                'gender' => $gender,
                'birth_place' => 'Bandung',
                'birth_date' => now()->subYears(16)->subDays($i * 11)->toDateString(),
                'classroom_id' => $classroom->id,
                'guardian_id' => $guardian->id,
                'address' => 'Jl. Kenanga No. '.($i + 1).', Bandung',
                'status' => 'aktif',
                'entry_year' => $classroom->grade_level === 10 ? 2026 : 2025,
            ]);
        }

        $assignmentsByClass = collect($assignments)->groupBy('classroom_id');
        $types = ['tugas', 'tugas', 'uts', 'uas'];
        $attendanceStatuses = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'sakit', 'izin', 'hadir', 'hadir', 'hadir', 'hadir', 'alpa'];

        $schoolDays = collect(range(1, 30))
            ->map(fn ($ago) => now()->subDays($ago))
            ->filter(fn ($date) => $date->isWeekday())
            ->take(20)
            ->values();

        foreach ($students as $student) {
            $classAssignments = $assignmentsByClass[$student->classroom_id]->take(6);

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
                    'status' => $attendanceStatuses[array_rand($attendanceStatuses)],
                    'note' => null,
                ]);
            }
        }
    }

    private function createStudentUser(string $name, string $email): int
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'siswa123',
            'role' => User::ROLE_SISWA,
            'is_active' => true,
        ])->id;
    }

    private function seedStaffAndOrganization(): void
    {
        $kepalaTu = Staff::create([
            'nip' => '198704152011012008',
            'name' => 'Wati Sumiati, S.A.P.',
            'photo_path' => $this->copyPhoto('staf-tu-wati.jpg'),
            'gender' => 'P',
            'position' => 'Kepala Tata Usaha',
            'phone' => '0813-7000-1001',
            'email' => 'tatausaha@sekolah.id',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Staff::create([
            'nip' => '199002202015011003',
            'name' => 'Hadi Supriyanto',
            'gender' => 'L',
            'position' => 'Staf Administrasi',
            'phone' => '0813-7000-1002',
            'email' => 'hadi.supriyanto@sekolah.id',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Staff::create([
            'nip' => '199508102019022005',
            'name' => 'Rina Marlina',
            'gender' => 'P',
            'position' => 'Staf Kesiswaan',
            'phone' => '0813-7000-1003',
            'email' => 'rina.marlina@sekolah.id',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $root = OrganizationMember::create([
            'name' => 'Dra. Siti Rahmawati, M.Pd.',
            'position' => 'Kepala Sekolah',
            'photo_path' => $this->copyPhoto('kepala-sekolah.jpg'),
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Ahmad Fauzi, M.Pd.',
            'position' => 'Wakil Kepala Bidang Kurikulum',
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Ratna Dewi, S.Si.',
            'position' => 'Wakil Kepala Bidang Kesiswaan',
            'sort_order' => 2,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Budi Santoso, S.Pd.',
            'position' => 'Wakil Kepala Bidang Sarana Prasarana',
            'photo_path' => $this->copyPhoto('guru-budi.jpg'),
            'sort_order' => 3,
        ]);

        $tuNode = OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => $kepalaTu->name,
            'position' => 'Kepala Tata Usaha',
            'photo_path' => $kepalaTu->photo_path,
            'sort_order' => 4,
        ]);

        OrganizationMember::create([
            'parent_id' => $tuNode->id,
            'name' => 'Hadi Supriyanto',
            'position' => 'Staf Administrasi',
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $tuNode->id,
            'name' => 'Rina Marlina',
            'position' => 'Staf Kesiswaan',
            'sort_order' => 2,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Sri Wahyuni, S.Pd.',
            'position' => 'Koordinator Bimbingan Konseling',
            'photo_path' => $this->copyPhoto('guru-sri.jpg'),
            'sort_order' => 5,
        ]);
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
