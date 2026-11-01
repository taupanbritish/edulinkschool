<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->items() as $item) {
            $createdAt = now()->subHours($item['hours_ago']);

            $message = ContactMessage::firstOrNew([
                'email' => $item['email'],
                'subject' => $item['subject'],
            ]);

            $message->fill([
                'name' => $item['name'],
                'phone' => $item['phone'],
                'message' => $item['message'],
                'is_read' => $item['is_read'],
                'read_at' => $item['is_read'] ? $createdAt->copy()->addMinutes(45) : null,
                'ip_address' => $item['ip_address'],
            ]);

            $message->created_at = $createdAt;
            $message->updated_at = $createdAt;
            $message->save();
        }
    }

    private function items(): array
    {
        return [
            [
                'name' => 'Rina Marlina',
                'email' => 'rina.marlina@example.com',
                'phone' => '0813-2000-1101',
                'subject' => 'Pendaftaran Peserta Didik Baru 2027/2028',
                'message' => 'Selamat pagi. Saya ingin menanyakan jadwal pendaftaran peserta didik baru tahun pelajaran 2027/2028. Apakah pendaftaran dibuka secara online atau datang langsung ke sekolah? Mohon informasi mengenai syarat dan biaya pendaftarannya. Terima kasih.',
                'is_read' => false,
                'hours_ago' => 2,
                'ip_address' => '103.20.10.11',
            ],
            [
                'name' => 'Bambang Saputra',
                'email' => 'bambang.saputra@example.com',
                'phone' => '0812-3000-2202',
                'subject' => 'Permohonan Surat Keterangan Siswa Aktif',
                'message' => 'Saya Bambang Saputra, orang tua dari Bagas Saputra kelas X-1. Anak saya membutuhkan surat keterangan siswa aktif untuk keperluan pengajuan beasiswa. Apakah surat dapat diambil di tata usaha pada hari kerja? Mohon arahan prosedurnya.',
                'is_read' => false,
                'hours_ago' => 27,
                'ip_address' => '103.20.10.12',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'phone' => '0821-4000-3303',
                'subject' => 'Saran Penambahan Koleksi Buku Perpustakaan',
                'message' => 'Terima kasih atas pengelolaan perpustakaan yang nyaman. Saya menyarankan penambahan koleksi buku sains populer dan novel remaja agar siswa semakin tertarik membaca. Semoga saran ini bermanfaat.',
                'is_read' => true,
                'hours_ago' => 74,
                'ip_address' => '103.20.10.13',
            ],
            [
                'name' => 'Fajar Nugroho',
                'email' => 'fajar.nugroho@example.com',
                'phone' => '0857-5000-4404',
                'subject' => 'Informasi Ekstrakurikuler Robotika',
                'message' => 'Selamat siang. Anak saya sangat tertarik dengan robotika. Apakah ekstrakurikuler robotika tersedia untuk siswa kelas X, dan kapan jadwal latihannya? Apakah ada biaya tambahan untuk perlengkapan?',
                'is_read' => true,
                'hours_ago' => 120,
                'ip_address' => '103.20.10.14',
            ],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra@karyanusantara.co.id',
                'phone' => '022-7000-5505',
                'subject' => 'Kerja Sama Program Magang Siswa',
                'message' => 'Kami dari PT Karya Nusantara bermaksud menjajaki kerja sama program magang bagi siswa kelas XI. Kami dapat menyediakan kuota magang di divisi administrasi dan teknologi informasi. Mohon informasi kontak bagian yang dapat kami temui untuk pembahasan lebih lanjut.',
                'is_read' => false,
                'hours_ago' => 168,
                'ip_address' => '103.20.10.15',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@example.com',
                'phone' => '0838-6000-6606',
                'subject' => 'Apresiasi Kegiatan Pentas Seni',
                'message' => 'Saya ingin menyampaikan apresiasi kepada seluruh panitia dan siswa atas kegiatan pentas seni yang sangat meriah. Acara berjalan tertib dan anak-anak tampil percaya diri. Terima kasih kepada para guru yang telah membimbing.',
                'is_read' => true,
                'hours_ago' => 240,
                'ip_address' => '103.20.10.16',
            ],
        ];
    }
}
