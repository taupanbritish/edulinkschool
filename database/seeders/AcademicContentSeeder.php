<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\Achievement;
use App\Models\AlumniDestination;
use App\Models\CurriculumGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AcademicContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAchievements();
        $this->seedCurriculumGroups();
        $this->seedAcademicEvents();
        $this->seedAlumniDestinations();
    }

    private function seedAchievements(): void
    {
        foreach ($this->achievements() as $index => $item) {
            Achievement::updateOrCreate(
                ['title' => $item['title']],
                [
                    'category' => $item['category'],
                    'recipient' => $item['recipient'],
                    'level' => $item['level'],
                    'achieved_at' => $item['achieved_at'],
                    'description' => $item['description'],
                    'photo_path' => $this->copyPhoto($item['photo']),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedCurriculumGroups(): void
    {
        foreach ($this->curriculumGroups() as $index => $group) {
            CurriculumGroup::updateOrCreate(
                ['name' => $group['name']],
                [
                    'description' => $group['description'],
                    'subjects' => $group['subjects'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedAcademicEvents(): void
    {
        foreach ($this->academicEvents() as $event) {
            AcademicEvent::updateOrCreate(
                ['title' => $event['title'], 'starts_at' => $event['starts_at']],
                [
                    'category' => $event['category'],
                    'ends_at' => $event['ends_at'] ?? null,
                    'description' => $event['description'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedAlumniDestinations(): void
    {
        foreach ($this->alumniDestinations() as $index => $alumni) {
            $destination = AlumniDestination::firstOrNew([
                'name' => $alumni['name'],
                'university' => $alumni['university'],
            ]);

            $destination->fill([
                'major' => $alumni['major'],
                'graduation_year' => $alumni['graduation_year'],
                'type' => $alumni['type'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            if (blank($destination->photo_path) && isset($alumni['photo'])) {
                $destination->photo_path = $this->copyPhoto($alumni['photo']);
            }

            $destination->save();
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

    private function achievements(): array
    {
        return [
            [
                'category' => 'akademik',
                'title' => 'Medali Emas OSN Informatika 2026',
                'recipient' => 'Rizky Pratama',
                'level' => 'Nasional',
                'achieved_at' => '2026-09-15',
                'description' => 'Meraih medali emas bidang Informatika pada Olimpiade Sains Nasional 2026 setelah melalui seleksi kota, provinsi, dan nasional.',
                'photo' => 'berita-osn.jpg',
            ],
            [
                'category' => 'akademik',
                'title' => 'Juara 1 Lomba Karya Tulis Ilmiah Kota Bandung',
                'recipient' => 'Ananda Putri',
                'level' => 'Kota',
                'achieved_at' => '2026-08-10',
                'description' => 'Karya tulis tentang pengelolaan sampah sekolah meraih juara pertama pada lomba karya tulis ilmiah tingkat kota.',
                'photo' => 'berita-literasi.jpg',
            ],
            [
                'category' => 'akademik',
                'title' => 'Medali Perak Olimpiade Matematika Provinsi',
                'recipient' => 'Tim Olimpiade Sains',
                'level' => 'Provinsi',
                'achieved_at' => '2026-07-22',
                'description' => 'Tim olimpiade matematika sekolah berhasil meraih medali perak pada seleksi tingkat provinsi Jawa Barat.',
                'photo' => 'berita-kunjungan.jpg',
            ],
            [
                'category' => 'non_akademik',
                'title' => 'Juara 2 Kompetisi Robotik Nasional',
                'recipient' => 'Tim Robotika Edulink',
                'level' => 'Nasional',
                'achieved_at' => '2026-08-30',
                'description' => 'Robot line follower karya siswa meraih juara kedua pada kompetisi robotik pelajar tingkat nasional.',
                'photo' => 'berita-robotika.jpg',
            ],
            [
                'category' => 'non_akademik',
                'title' => 'Juara 1 Festival Band Pelajar',
                'recipient' => 'Band Edulink',
                'level' => 'Kota',
                'achieved_at' => '2026-09-20',
                'description' => 'Ekstrakurikuler musik sekolah tampil sebagai juara pertama pada festival band pelajar se-Kota Bandung.',
                'photo' => 'berita-hari-guru.jpg',
            ],
            [
                'category' => 'non_akademik',
                'title' => 'Juara 3 Futsal Pelajar Provinsi',
                'recipient' => 'Tim Futsal Putra',
                'level' => 'Provinsi',
                'achieved_at' => '2026-06-18',
                'description' => 'Tim futsal putra sekolah meraih peringkat ketiga pada turnamen futsal pelajar tingkat provinsi.',
                'photo' => 'berita-hari-guru.jpg',
            ],
        ];
    }

    private function curriculumGroups(): array
    {
        return [
            [
                'name' => 'Kelompok Mata Pelajaran Wajib',
                'description' => 'Diikuti seluruh siswa untuk membangun kompetensi dasar, karakter, dan literasi lintas disiplin.',
                'subjects' => "Pendidikan Agama dan Budi Pekerti\nPendidikan Pancasila\nBahasa Indonesia\nBahasa Inggris\nMatematika\nPendidikan Jasmani, Olahraga, dan Kesehatan\nInformatika\nSeni dan Budaya",
            ],
            [
                'name' => 'Peminatan Matematika dan Sains (MIPA)',
                'description' => 'Untuk siswa yang berminat mendalami sains, teknologi, teknik, dan kesehatan.',
                'subjects' => "Matematika Lanjut\nFisika\nKimia\nBiologi",
            ],
            [
                'name' => 'Peminatan Ilmu Sosial (IPS)',
                'description' => 'Mengembangkan pemahaman sosial, ekonomi, dan kebijakan publik bagi calon pemimpin masa depan.',
                'subjects' => "Sejarah\nGeografi\nEkonomi\nSosiologi",
            ],
            [
                'name' => 'Peminatan Bahasa dan Budaya',
                'description' => 'Memperkuat kemampuan berbahasa asing dan pemahaman budaya untuk studi lanjut di dalam maupun luar negeri.',
                'subjects' => "Bahasa Indonesia Lanjut\nBahasa Inggris Lanjut\nBahasa Jepang\nAntropologi",
            ],
        ];
    }

    private function academicEvents(): array
    {
        return [
            [
                'title' => 'Masa Pengenalan Lingkungan Sekolah',
                'category' => 'kegiatan',
                'starts_at' => '2026-07-13',
                'ends_at' => '2026-07-15',
                'description' => 'Orientasi siswa baru, pengenalan lingkungan, tata tertib, dan kegiatan kebersamaan.',
            ],
            [
                'title' => 'Upacara HUT Kemerdekaan RI',
                'category' => 'agenda',
                'starts_at' => '2026-08-17',
                'description' => 'Upacara bendera dan rangkaian lomba kemerdekaan di lapangan sekolah.',
            ],
            [
                'title' => 'Penilaian Tengah Semester Ganjil',
                'category' => 'ujian',
                'starts_at' => '2026-09-21',
                'ends_at' => '2026-09-26',
                'description' => 'Penilaian tengah semester untuk seluruh tingkat. Jadwal per kelas diumumkan melalui wali kelas.',
            ],
            [
                'title' => 'Pekan Olahraga dan Seni',
                'category' => 'kegiatan',
                'starts_at' => '2026-10-05',
                'ends_at' => '2026-10-09',
                'description' => 'Kompetisi antar kelas dalam bidang olahraga, seni, dan kreativitas.',
            ],
            [
                'title' => 'Penilaian Akhir Semester Ganjil',
                'category' => 'ujian',
                'starts_at' => '2026-12-07',
                'ends_at' => '2026-12-12',
                'description' => 'Penilaian akhir semester ganjil tahun pelajaran 2026/2027.',
            ],
            [
                'title' => 'Libur Semester Ganjil',
                'category' => 'libur',
                'starts_at' => '2026-12-21',
                'ends_at' => '2027-01-02',
                'description' => 'Libur akhir semester ganjil. Kegiatan belajar dimulai kembali pada 5 Januari 2027.',
            ],
            [
                'title' => 'Ujian Sekolah Kelas XII',
                'category' => 'ujian',
                'starts_at' => '2027-03-15',
                'ends_at' => '2027-03-20',
                'description' => 'Ujian sekolah dan ujian praktik untuk kelas XII sebagai syarat kelulusan.',
            ],
            [
                'title' => 'Libur Semester Genap',
                'category' => 'libur',
                'starts_at' => '2027-06-14',
                'ends_at' => '2027-07-10',
                'description' => 'Libur akhir tahun pelajaran 2026/2027.',
            ],
        ];
    }

    private function alumniDestinations(): array
    {
        return [
            ['name' => 'Ananda Putri', 'university' => 'Institut Teknologi Bandung', 'major' => 'Teknik Informatika', 'graduation_year' => 2025, 'type' => 'ptn', 'photo' => 'alumni-ananda-putri.jpg'],
            ['name' => 'Rizky Pratama', 'university' => 'Universitas Indonesia', 'major' => 'Kedokteran', 'graduation_year' => 2025, 'type' => 'ptn', 'photo' => 'alumni-rizky-pratama.jpg'],
            ['name' => 'Salsabila Rahma', 'university' => 'Universitas Gadjah Mada', 'major' => 'Hukum', 'graduation_year' => 2025, 'type' => 'ptn', 'photo' => 'alumni-salsabila-rahma.jpg'],
            ['name' => 'Bagas Saputra', 'university' => 'Institut Teknologi Sepuluh Nopember', 'major' => 'Teknik Mesin', 'graduation_year' => 2024, 'type' => 'ptn', 'photo' => 'alumni-bagas-saputra.jpg'],
            ['name' => 'Nayla Zahra', 'university' => 'Universitas Padjadjaran', 'major' => 'Farmasi', 'graduation_year' => 2024, 'type' => 'ptn'],
            ['name' => 'Fikri Ramadhan', 'university' => 'Universitas Airlangga', 'major' => 'Akuntansi', 'graduation_year' => 2024, 'type' => 'ptn', 'photo' => 'alumni-fikri-ramadhan.jpg'],
            ['name' => 'Intan Permata', 'university' => 'Universitas Diponegoro', 'major' => 'Psikologi', 'graduation_year' => 2023, 'type' => 'ptn'],
            ['name' => 'Yoga Pratama', 'university' => 'Telkom University', 'major' => 'Desain Komunikasi Visual', 'graduation_year' => 2023, 'type' => 'universitas_ternama'],
            ['name' => 'Citra Lestari', 'university' => 'Universitas Bina Nusantara', 'major' => 'Sistem Informasi', 'graduation_year' => 2023, 'type' => 'universitas_ternama'],
            ['name' => 'Dimas Nugraha', 'university' => 'Universitas Multimedia Nusantara', 'major' => 'Ilmu Komunikasi', 'graduation_year' => 2022, 'type' => 'universitas_ternama'],
        ];
    }
}
