<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->facilities() as $index => $item) {
            Facility::updateOrCreate(
                ['name' => $item['name']],
                [
                    'description' => $item['description'],
                    'photo_path' => $this->copyPhoto($item['photo']),
                    'video_url' => $item['video'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }

        foreach ($this->extracurriculars() as $index => $item) {
            Extracurricular::updateOrCreate(
                ['name' => $item['name']],
                [
                    'description' => $item['description'],
                    'photo_path' => $this->copyPhoto($item['photo']),
                    'video_url' => $item['video'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
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

    private function facilities(): array
    {
        return [
            [
                'name' => 'Perpustakaan Sekolah',
                'description' => "Perpustakaan dengan lebih dari 12.000 judul buku, ruang baca ber-AC, dan katalog digital yang dapat diakses siswa.\n\nBuka setiap hari kerja pukul 07.00–16.00 WIB dengan layanan peminjaman dan pojok literasi.",
                'photo' => 'berita-literasi.jpg',
                'video' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            ],
            [
                'name' => 'Laboratorium Komputer',
                'description' => "Dua laboratorium komputer dengan 40 unit PC, jaringan internet berkecepatan tinggi, serta perangkat pendukung robotika dan desain grafis.\n\nDigunakan untuk pembelajaran informatika, praktik ujian berbasis komputer, dan pembinaan ekstrakurikuler.",
                'photo' => 'berita-robotika.jpg',
                'video' => 'https://youtu.be/ScMzIvxBSi4',
            ],
            [
                'name' => 'Aula Serbaguna',
                'description' => 'Aula berkapasitas 500 orang untuk upacara, pentas seni, seminar, rapat orang tua, dan kegiatan pengembangan diri siswa.',
                'photo' => 'berita-hari-guru.jpg',
                'video' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
            ],
            [
                'name' => 'Ruang Kelas Modern',
                'description' => "Ruang kelas dengan proyektor, pendingin ruangan, dan tata letak fleksibel untuk pembelajaran kolaboratif.\n\nSetiap kelas didampingi wali kelas yang memantau perkembangan belajar dan karakter siswa.",
                'photo' => 'pengumuman-uts.jpg',
                'video' => 'https://youtu.be/aqz-KE-bpKQ',
            ],
        ];
    }

    private function extracurriculars(): array
    {
        return [
            [
                'name' => 'Robotika',
                'description' => "Klub robotika melatih siswa merancang, merakit, dan memprogram robot untuk kompetisi tingkat kota hingga nasional.\n\nLatihan setiap Rabu pukul 14.30–16.30 WIB di laboratorium komputer.",
                'photo' => 'berita-robotika.jpg',
                'video' => 'https://youtu.be/ScMzIvxBSi4',
            ],
            [
                'name' => 'Literasi & Jurnalistik',
                'description' => 'Wadah bagi siswa yang gemar menulis, membaca, dan meliput kegiatan sekolah. Hasil karya dimuat di majalah dinding serta kanal berita sekolah.',
                'photo' => 'berita-literasi.jpg',
                'video' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            ],
            [
                'name' => 'Olimpiade Sains',
                'description' => "Pembinaan intensif Matematika, Fisika, Kimia, Biologi, dan Informatika untuk persiapan OSN dan kompetisi sains lainnya.\n\nPendampingan dilakukan guru mapel dan alumni berprestasi setiap Jumat sore.",
                'photo' => 'berita-osn.jpg',
                'video' => 'https://youtu.be/aqz-KE-bpKQ',
            ],
            [
                'name' => 'Seni & Paduan Suara',
                'description' => 'Mengembangkan bakat musik, tari, dan teater siswa. Tim paduan suara tampil pada upacara sekolah, hari besar nasional, dan lomba antar sekolah.',
                'photo' => 'berita-hari-guru.jpg',
                'video' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
            ],
            [
                'name' => 'Studi Wisata & Karya Ilmiah',
                'description' => 'Kegiatan kunjungan industri dan penelitian lapangan yang melatih siswa menyusun laporan ilmiah serta mempresentasikan hasil temuannya.',
                'photo' => 'berita-kunjungan.jpg',
                'video' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
            ],
        ];
    }
}
