<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementGallerySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();

        foreach ($this->items() as $item) {
            $announcement = Announcement::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'user_id' => $admin->id,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'category' => $item['category'],
                    'is_published' => true,
                    'published_at' => now()->subDays($item['days_ago']),
                ],
            );

            $order = 0;

            foreach ($item['photos'] ?? [] as $photo) {
                $path = $this->copyPhoto($photo['file']);

                if ($path === null) {
                    continue;
                }

                $announcement->attachments()->updateOrCreate(
                    ['path' => $path],
                    ['type' => 'foto', 'caption' => $photo['caption'], 'sort_order' => ++$order],
                );
            }

            foreach ($item['videos'] ?? [] as $video) {
                $announcement->attachments()->updateOrCreate(
                    ['url' => $video['url']],
                    ['type' => 'video', 'caption' => $video['caption'], 'sort_order' => ++$order],
                );
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

    private function items(): array
    {
        return [
            [
                'title' => 'Penerimaan Rapor Semester Ganjil 2026/2027',
                'category' => 'pengumuman',
                'days_ago' => 3,
                'excerpt' => 'Pembagian rapor semester ganjil dilaksanakan pada Sabtu, 19 Desember 2026 di ruang kelas masing-masing.',
                'body' => "Pembagian rapor semester ganjil tahun pelajaran 2026/2027 akan dilaksanakan pada:\n\nHari/tanggal: Sabtu, 19 Desember 2026\nWaktu: 08.00 – 11.00 WIB\nTempat: Ruang kelas masing-masing\n\nRapor diserahkan langsung kepada orang tua/wali. Mohon kehadiran tepat waktu dan membawa kartu identitas. Siswa yang berhalangan hadir dapat mengambil rapor di bagian tata usaha pada hari kerja berikutnya.",
                'photos' => [
                    ['file' => 'pengumuman-rapor.jpg', 'caption' => 'Orang tua menerima rapor semester ganjil di aula sekolah.'],
                ],
            ],
            [
                'title' => 'Siswa Edulink School Raih Medali Emas OSN Informatika',
                'category' => 'berita',
                'days_ago' => 6,
                'excerpt' => 'Tim olimpiade sains sekolah berhasil membawa pulang satu medali emas dan dua medali perak pada OSN tingkat nasional.',
                'body' => "Prestasi membanggakan kembali diraih oleh siswa SMA Edulink School pada ajang Olimpiade Sains Nasional (OSN) 2026. Perwakilan sekolah berhasil meraih satu medali emas pada bidang Informatika serta dua medali perak pada bidang Matematika dan Biologi.\n\nKepala sekolah menyampaikan apresiasi kepada siswa, guru pembimbing, dan orang tua yang telah mendukung persiapan selama enam bulan. Prestasi ini menjadi motivasi bagi seluruh warga sekolah untuk terus berkembang.",
                'photos' => [
                    ['file' => 'berita-osn.jpg', 'caption' => 'Tiga peraih medali OSN 2026 bersama sertifikat penghargaan.'],
                ],
                'videos' => [
                    ['url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE', 'caption' => 'Cuplikan prosesi penyerahan medali OSN 2026.'],
                ],
            ],
            [
                'title' => 'Jadwal Ujian Tengah Semester Ganjil',
                'category' => 'pengumuman',
                'days_ago' => 9,
                'excerpt' => 'UTS ganjil dilaksanakan 5–10 Oktober 2026. Simak jadwal lengkap dan tata tertib ujian.',
                'body' => "Ujian Tengah Semester (UTS) ganjil akan dilaksanakan pada 5–10 Oktober 2026. Seluruh siswa diharapkan hadir 15 menit sebelum ujian dimulai dan membawa kartu peserta.\n\nMata pelajaran yang diujikan meliputi Matematika, Bahasa Indonesia, Bahasa Inggris, Fisika, Kimia, Biologi, Sejarah, dan PJOK. Jadwal per kelas dapat dilihat pada papan pengumuman dan aplikasi mobile sekolah.",
                'photos' => [
                    ['file' => 'pengumuman-uts.jpg', 'caption' => 'Suasana ujian tengah semester di ruang kelas.'],
                ],
                'videos' => [
                    ['url' => 'https://youtu.be/jNQXAC9IVRw', 'caption' => 'Pesan kepala sekolah menjelang pelaksanaan UTS.'],
                ],
            ],
            [
                'title' => 'Program Literasi Jumat: Membaca 30 Menit Sebelum KBM',
                'category' => 'berita',
                'days_ago' => 12,
                'excerpt' => 'Gerakan literasi sekolah dilanjutkan setiap Jumat pagi dengan membaca buku non-fiksi pilihan siswa.',
                'body' => "Mulai September 2026, seluruh siswa mengikuti program literasi setiap Jumat pukul 07.00–07.30 WIB. Program ini bertujuan menumbuhkan kebiasaan membaca dan kemampuan berpikir kritis.\n\nSiswa bebas memilih buku dari perpustakaan atau koleksi pribadi. Setiap bulan, siswa menuliskan resensi singkat yang akan dinilai sebagai bagian dari penilaian Bahasa Indonesia.",
                'photos' => [
                    ['file' => 'berita-literasi.jpg', 'caption' => 'Siswa membaca buku pilihan pada program literasi Jumat.'],
                ],
            ],
            [
                'title' => 'Kunjungan Industri Kelas XI ke Bandung Techno Park',
                'category' => 'berita',
                'days_ago' => 15,
                'excerpt' => 'Kelas XI mengikuti kunjungan industri untuk mengenal dunia kerja di bidang teknologi dan kewirausahaan.',
                'body' => "Sebanyak 120 siswa kelas XI mengikuti kunjungan industri ke Bandung Techno Park pada 22 September 2026. Kegiatan ini merupakan bagian dari projek penguatan profil pelajar Pancasila tema kewirausahaan.\n\nSiswa berkesempatan berdiskusi dengan praktisi industri, mengamati proses kerja tim produk digital, dan menyusun laporan refleksi yang dipresentasikan di kelas.",
                'photos' => [
                    ['file' => 'berita-kunjungan.jpg', 'caption' => 'Siswa kelas XI mengikuti sesi presentasi di Bandung Techno Park.'],
                ],
                'videos' => [
                    ['url' => 'https://youtu.be/aqz-KE-bpKQ', 'caption' => 'Rekap kegiatan kunjungan industri kelas XI.'],
                ],
            ],
            [
                'title' => 'Ekstrakurikuler Baru: Robotika dan Desain Grafis',
                'category' => 'pengumuman',
                'days_ago' => 18,
                'excerpt' => 'Pendaftaran ekstrakurikuler Robotika dan Desain Grafis dibuka hingga 30 September 2026.',
                'body' => "Sekolah membuka dua ekstrakurikuler baru tahun ini: Robotika dan Desain Grafis. Kegiatan dilaksanakan setiap Rabu pukul 14.30–16.30 WIB di laboratorium komputer.\n\nPendaftaran dibuka hingga 30 September 2026 melalui wali kelas. Kuota masing-masing ekstrakurikuler 30 siswa.",
                'photos' => [
                    ['file' => 'berita-robotika.jpg', 'caption' => 'Siswa merakit robot line follower di laboratorium komputer.'],
                ],
                'videos' => [
                    ['url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'caption' => 'Demo robot line follower hasil karya siswa.'],
                ],
            ],
            [
                'title' => 'Peringatan Hari Guru Nasional 2026',
                'category' => 'berita',
                'days_ago' => 21,
                'excerpt' => 'Upacara dan pentas seni siswa memeriahkan Hari Guru Nasional di lapangan sekolah.',
                'body' => 'Seluruh warga sekolah mengikuti upacara peringatan Hari Guru Nasional pada 25 November 2026. Acara dilanjutkan dengan pentas seni persembahan siswa untuk para guru, pemberian penghargaan guru inspiratif, dan ramah tamah di aula sekolah.',
                'photos' => [
                    ['file' => 'berita-hari-guru.jpg', 'caption' => 'Pentas seni siswa dalam peringatan Hari Guru Nasional 2026.'],
                ],
                'videos' => [
                    ['url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE', 'caption' => 'Sorotan acara peringatan Hari Guru Nasional.'],
                ],
            ],
            [
                'title' => 'Pengambilan Seragam dan Buku Paket Siswa Baru',
                'category' => 'pengumuman',
                'days_ago' => 24,
                'excerpt' => 'Pengambilan seragam dan buku paket siswa baru dilayani 1–5 Agustus 2026 di lobi sekolah.',
                'body' => "Pengambilan seragam dan buku paket untuk siswa baru tahun pelajaran 2026/2027 dilayani pada 1–5 Agustus 2026 pukul 08.00–14.00 WIB di lobi sekolah.\n\nMohon membawa bukti registrasi dan kartu identitas orang tua. Pengambilan dapat diwakilkan dengan surat kuasa sederhana.",
                'photos' => [
                    ['file' => 'pengumuman-seragam.jpg', 'caption' => 'Orang tua mengambil seragam dan buku paket siswa baru.'],
                ],
            ],
        ];
    }
}
