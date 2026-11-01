<?php

namespace Database\Seeders;

use App\Models\CounselingService;
use App\Models\StudentOrganization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StudentLifeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOrganizations();
        $this->seedCounselingServices();
    }

    private function seedOrganizations(): void
    {
        foreach ($this->organizations() as $index => $organization) {
            StudentOrganization::updateOrCreate(
                ['name' => $organization['name']],
                [
                    'short_name' => $organization['short_name'],
                    'description' => $organization['description'],
                    'advisor' => $organization['advisor'],
                    'vision' => $organization['vision'],
                    'mission' => $organization['mission'],
                    'programs' => $organization['programs'],
                    'photo_path' => $this->copyPhoto($organization['photo']),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedCounselingServices(): void
    {
        foreach ($this->counselingServices() as $index => $service) {
            CounselingService::updateOrCreate(
                ['title' => $service['title']],
                [
                    'category' => $service['category'],
                    'description' => $service['description'],
                    'schedule' => $service['schedule'],
                    'contact' => $service['contact'],
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

    private function organizations(): array
    {
        return [
            [
                'name' => 'Organisasi Siswa Intra Sekolah',
                'short_name' => 'OSIS',
                'description' => 'OSIS adalah organisasi resmi siswa yang menjadi wadah kepemimpinan, kreativitas, dan pelayanan bagi seluruh warga sekolah. OSIS menyelenggarakan kegiatan sekolah, menyalurkan aspirasi siswa, serta mendorong partisipasi aktif dalam kehidupan sekolah.',
                'advisor' => 'Rina Kartika, S.Pd.',
                'vision' => 'Terwujudnya siswa yang aktif, kreatif, berintegritas, dan berjiwa kepemimpinan melalui kegiatan yang positif dan inklusif.',
                'mission' => "Menumbuhkan sikap disiplin, tanggung jawab, dan saling menghormati di lingkungan sekolah.\nMenjadi jembatan komunikasi antara siswa, guru, dan pihak sekolah.\nMenyelenggarakan kegiatan yang mengembangkan minat, bakat, dan kreativitas siswa.\nMembangun budaya pelayanan dan kepedulian sosial.",
                'programs' => "Pekan Olahraga dan Seni\nBakti Sosial\nLatihan Dasar Kepemimpinan Siswa\nPeringatan Hari Besar Nasional",
                'photo' => 'berita-hari-guru.jpg',
            ],
            [
                'name' => 'Majelis Perwakilan Kelas',
                'short_name' => 'MPK',
                'description' => 'MPK merupakan lembaga perwakilan siswa yang menjembatani aspirasi setiap kelas. MPK mengawasi kinerja OSIS, menyusun rekomendasi program sekolah, dan memastikan setiap kebijakan berpihak pada kepentingan siswa.',
                'advisor' => 'Dedi Kurniawan, S.Pd.',
                'vision' => 'Menjadi lembaga perwakilan siswa yang aspiratif, transparan, dan akuntabel dalam mendukung terwujudnya sekolah yang demokratis.',
                'mission' => "Menampung dan menyalurkan aspirasi siswa secara terbuka dan bertanggung jawab.\nMengawasi pelaksanaan program kerja OSIS.\nMembangun komunikasi yang sehat antara siswa, OSIS, dan pihak sekolah.\nMendorong evaluasi program yang berkelanjutan.",
                'programs' => "Forum Aspirasi Siswa\nPengawasan Kinerja OSIS\nMusyawarah Perwakilan Kelas\nEvaluasi Program Sekolah",
                'photo' => 'berita-kunjungan.jpg',
            ],
        ];
    }

    private function counselingServices(): array
    {
        return [
            [
                'category' => 'layanan',
                'title' => 'Konsultasi Pribadi & Sosial',
                'description' => 'Ruang aman untuk membicarakan persoalan pribadi, pertemanan, dan penyesuaian diri. Konselor mendengarkan tanpa menghakimi dan membantu siswa menemukan langkah penyelesaian.',
                'schedule' => 'Senin–Jumat, 08.00–14.00 WIB',
                'contact' => 'Ruang BK · guru.bk@sekolah.id',
            ],
            [
                'category' => 'layanan',
                'title' => 'Konsultasi Akademik',
                'description' => 'Pendampingan belajar, manajemen waktu, dan strategi menghadapi ujian bagi siswa yang membutuhkan dukungan tambahan.',
                'schedule' => 'Senin–Kamis, 09.00–13.00 WIB',
                'contact' => 'Ruang BK · (022) 1234567',
            ],
            [
                'category' => 'layanan',
                'title' => 'Konsultasi Karier',
                'description' => 'Arahan memilih jurusan, mengenali minat dan bakat, serta menyiapkan rencana studi lanjut ke perguruan tinggi.',
                'schedule' => 'Selasa & Kamis, 10.00–14.00 WIB',
                'contact' => 'Ruang BK · guru.bk@sekolah.id',
            ],
            [
                'category' => 'layanan',
                'title' => 'Konsultasi Keluarga',
                'description' => 'Dukungan bagi siswa yang menghadapi persoalan keluarga, dengan pendekatan yang menjaga privasi dan melibatkan orang tua bila diperlukan.',
                'schedule' => 'Dengan perjanjian',
                'contact' => 'Ruang BK · WhatsApp sekolah',
            ],
            [
                'category' => 'program',
                'title' => 'Pembinaan Karakter Mingguan',
                'description' => 'Sesi pembinaan karakter setiap Jumat yang membahas integritas, disiplin, empati, dan tanggung jawab melalui diskusi dan refleksi bersama.',
                'schedule' => 'Jumat, 07.30–08.15 WIB',
                'contact' => 'Wali kelas & guru BK',
            ],
            [
                'category' => 'program',
                'title' => 'Program Anti-Perundungan',
                'description' => 'Kampanye pencegahan perundungan, pembentukan agen perdamaian siswa, serta pendampingan korban dan pelaku secara berkeadilan.',
                'schedule' => 'Sepanjang tahun ajaran',
                'contact' => 'Ruang BK · guru.bk@sekolah.id',
            ],
            [
                'category' => 'program',
                'title' => 'Pelatihan Keterampilan Sosial',
                'description' => 'Melatih komunikasi asertif, empati, kerja sama, dan penyelesaian konflik melalui lokakarya dan permainan peran.',
                'schedule' => 'Sabtu (jadwal bergilir)',
                'contact' => 'Ruang BK · OSIS',
            ],
            [
                'category' => 'program',
                'title' => 'Mentoring Sebaya',
                'description' => 'Siswa terlatih mendampingi teman sebaya dalam belajar dan beradaptasi, didampingi guru BK sebagai pembina.',
                'schedule' => 'Rabu, 14.00–15.00 WIB',
                'contact' => 'Ruang BK · guru.bk@sekolah.id',
            ],
        ];
    }
}
