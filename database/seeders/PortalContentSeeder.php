<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\StudentWriting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSocialLinks();
        $this->seedWritings();
    }

    private function seedSocialLinks(): void
    {
        $links = [
            'social_instagram' => 'https://www.instagram.com/edulinkschool',
            'social_youtube' => 'https://www.youtube.com/@edulinkschool',
            'social_facebook' => 'https://www.facebook.com/edulinkschool',
            'social_tiktok' => 'https://www.tiktok.com/@edulinkschool',
        ];

        foreach ($links as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function seedWritings(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->orderBy('id')->first();

        foreach ($this->items() as $item) {
            $photo = $this->copyPhoto($item['photo']);
            $slug = Str::slug($item['title']);

            StudentWriting::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $admin?->id,
                    'title' => $item['title'],
                    'author_name' => $item['author_name'],
                    'author_type' => $item['author_type'],
                    'category' => $item['category'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'photo_path' => $photo,
                    'is_published' => true,
                    'published_at' => now()->subDays($item['days_ago']),
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

    private function items(): array
    {
        return [
            [
                'title' => 'Menumbuhkan Budaya Literasi di Ruang Kelas',
                'author_name' => 'Sri Wahyuni, S.Pd.',
                'author_type' => 'guru',
                'category' => 'artikel',
                'days_ago' => 4,
                'photo' => 'berita-literasi.jpg',
                'excerpt' => 'Literasi bukan sekadar kemampuan membaca, tetapi kebiasaan memahami dan menyampaikan gagasan.',
                'body' => "Budaya literasi tidak tumbuh dari satu kali tugas merangkum buku. Ia lahir dari kebiasaan kecil yang dilakukan berulang: membaca sepuluh menit sebelum pelajaran, menuliskan satu paragraf refleksi, lalu mendiskusikannya dengan teman sebangku.\n\nDi kelas saya, setiap Jumat dimulai dengan kegiatan \"baca bebas\". Siswa boleh memilih buku apa pun, lalu menuliskan satu kutipan favorit dan alasannya di catatan literasi. Tidak ada penilaian benar atau salah, hanya ruang untuk berpendapat.\n\nHasilnya tidak langsung terlihat dalam nilai ujian, tetapi perubahan itu ada: siswa lebih percaya diri bertanya, lebih teliti menyusun argumen, dan lebih terbuka terhadap sudut pandang lain. Literasi pada akhirnya adalah tentang belajar memahami dunia, bukan hanya memahami teks.",
            ],
            [
                'title' => 'Sepeda Tua dan Langit Sore di Ujung Jalan',
                'author_name' => 'Rizky Pratama',
                'author_type' => 'siswa',
                'category' => 'cerpen',
                'days_ago' => 9,
                'photo' => 'berita-kunjungan.jpg',
                'excerpt' => 'Cerita pendek tentang perjalanan pulang, sepeda tua, dan percakapan yang tertinggal di ujung jalan.',
                'body' => "Selepas lonceng terakhir, Raka selalu memilih jalan yang lebih panjang. Bukan karena jauh, tetapi karena di sana ada tanjakan kecil dengan langit sore yang lebar.\n\nSepedanya sudah tua. Rantainya berbunyi setiap kali dikayuh, seperti mengingatkan bahwa ia pernah menemani ayahnya bekerja. Raka tidak pernah mengeluh. Ia justru menyukai bunyi itu, karena di antara suara rantai dan angin, pikirannya terasa lebih tenang.\n\nDi ujung jalan, seorang lelaki tua berjualan es kelapa. Setiap hari ia menyapa dengan pertanyaan yang sama: \"Sudah pulang?\" Raka pun menjawab dengan kalimat yang sama, \"Belum, sebentar lagi.\" Percakapan singkat itu tidak pernah berubah, tetapi entah mengapa selalu membuat hari terasa utuh.\n\nMalam itu, Raka menulis di bukunya: kadang yang membuat perjalanan berharga bukan tujuannya, melainkan hal-hal kecil yang kita temui di sepanjang jalan.",
            ],
            [
                'title' => 'Belajar Bukan Perlombaan: Catatan Seorang Siswa',
                'author_name' => 'Citra Lestari',
                'author_type' => 'siswa',
                'category' => 'opini',
                'days_ago' => 14,
                'photo' => 'berita-hari-guru.jpg',
                'excerpt' => 'Nilai penting, tetapi bukan satu-satunya ukuran keberhasilan belajar.',
                'body' => "Saya sering mendengar kalimat \"belajar yang rajin supaya nilainya bagus\". Kalimat itu tidak salah, tetapi membuat belajar terasa seperti perlombaan yang harus dimenangkan.\n\nPadahal, belajar seharusnya tentang memahami sesuatu yang sebelumnya tidak kita pahami. Ada hari ketika nilai saya biasa saja, tetapi saya berhasil menjelaskan materi kepada teman yang kesulitan. Bagi saya, momen seperti itu jauh lebih bermakna daripada angka di kertas ujian.\n\nTentu saja nilai tetap penting. Ia adalah salah satu umpan balik. Namun jika nilai menjadi satu-satunya tujuan, kita kehilangan rasa ingin tahu yang justru menjadi akar dari semua pembelajaran.\n\nSaya berharap sekolah semakin sering memberi ruang untuk bertanya, mencoba, dan salah. Karena dari kesalahan itulah pemahaman yang sebenarnya tumbuh.",
            ],
            [
                'title' => 'Robot Sederhana, Pelajaran Besar',
                'author_name' => 'Bagas Saputra',
                'author_type' => 'siswa',
                'category' => 'artikel',
                'days_ago' => 20,
                'photo' => 'berita-robotika.jpg',
                'excerpt' => 'Pengalaman merakit robot sederhana mengajarkan kerja sama, kesabaran, dan cara berpikir sistematis.',
                'body' => "Awalnya kami hanya ingin membuat robot yang bisa berjalan lurus. Ternyata roda kirinya selalu berputar lebih cepat, sehingga robot kami berbelok ke kanan dan menabrak meja.\n\nKami menghabiskan dua jam untuk memeriksa rangkaian, mengganti baterai, dan mengatur ulang kode. Setiap percobaan gagal memberi petunjuk baru. Ketika akhirnya robot itu berjalan lurus, kami berteriak kegirangan seperti baru memenangkan pertandingan besar.\n\nDari kegiatan itu saya belajar bahwa masalah jarang selesai dengan satu jawaban. Kita perlu mengamati, mencoba, dan memperbaiki. Saya juga belajar mendengarkan teman yang punya ide berbeda, karena solusi terbaik sering datang dari diskusi.\n\nRobot kami memang sederhana, tetapi pelajarannya tidak sederhana: kerja sama dan ketekunan lebih berharga daripada hasil yang instan.",
            ],
        ];
    }
}
