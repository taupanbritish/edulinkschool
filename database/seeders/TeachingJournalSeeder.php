<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Models\TeachingJournalAttachment;
use App\Models\TeachingJournalAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TeachingJournalSeeder extends Seeder
{
    public function run(): void
    {
        $dates = collect(range(1, 14))
            ->map(fn (int $ago) => now()->subDays($ago)->startOfDay())
            ->filter(fn ($date) => $date->isWeekday())
            ->take(8)
            ->sort()
            ->values();

        $photoIndex = 0;

        foreach ($dates as $date) {
            $schedules = Schedule::with([
                'assignment.subject',
                'assignment.teacher',
                'assignment.classroom',
            ])
                ->where('day', $date->isoWeekday())
                ->orderBy('starts_at')
                ->get()
                ->groupBy(fn (Schedule $schedule) => $schedule->assignment?->classroom_id);

            foreach ($schedules as $classroomId => $sessions) {
                if (! $classroomId) {
                    continue;
                }

                $students = Student::where('classroom_id', $classroomId)
                    ->where('status', 'aktif')
                    ->orderBy('name')
                    ->get();

                if ($students->isEmpty()) {
                    continue;
                }

                foreach ($sessions->take(2) as $slot => $schedule) {
                    $assignment = $schedule->assignment;

                    if (! $assignment?->subject) {
                        continue;
                    }

                    $topic = $this->topicFor($assignment->subject->code, $date->day + $slot);

                    $journal = TeachingJournal::firstOrCreate(
                        [
                            'schedule_id' => $schedule->id,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'teaching_assignment_id' => $assignment->id,
                            'starts_at' => $schedule->starts_at,
                            'ends_at' => $schedule->ends_at,
                            'topic' => $topic['topic'],
                            'activity' => $topic['activity'],
                            'assignment' => $topic['assignment'],
                            'notes' => $topic['notes'],
                            'created_by' => $assignment->teacher?->user_id,
                        ],
                    );

                    foreach ($students as $index => $student) {
                        $status = $this->statusFor($student->id, $date->day, $slot, $index);

                        TeachingJournalAttendance::updateOrCreate(
                            ['journal_id' => $journal->id, 'student_id' => $student->id],
                            [
                                'status' => $status,
                                'note' => match ($status) {
                                    'sakit', 'izin' => 'Keterangan orang tua diterima',
                                    default => null,
                                },
                            ],
                        );
                    }

                    if ($slot === 0 && ! $journal->attachments()->exists()) {
                        $this->attachDemoPhoto($journal, $photoIndex++);
                    }
                }
            }
        }
    }

    /**
     * @return array{topic: string, activity: string, assignment: ?string, notes: ?string}
     */
    private function topicFor(string $code, int $seed): array
    {
        $entries = $this->topics()[$code] ?? $this->topics()['MTK'];

        return $entries[$seed % count($entries)];
    }

    private function statusFor(int $studentId, int $day, int $slot, int $index): string
    {
        $pool = [
            'hadir', 'hadir', 'hadir', 'hadir', 'hadir',
            'hadir', 'hadir', 'hadir', 'sakit', 'izin',
            'hadir', 'hadir', 'hadir', 'alpa',
        ];

        return $pool[($studentId + $day + $slot + $index) % count($pool)];
    }

    private function attachDemoPhoto(TeachingJournal $journal, int $index): void
    {
        $files = ['berita-literasi.jpg', 'berita-robotika.jpg', 'berita-kunjungan.jpg'];
        $file = $files[$index % count($files)];
        $source = database_path('seeders/photos/'.$file);

        if (! is_file($source)) {
            return;
        }

        $target = 'photos/demo/jurnal/jurnal-'.$journal->id.'.jpg';

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        TeachingJournalAttachment::firstOrCreate(
            ['journal_id' => $journal->id, 'path' => $target],
            [
                'type' => TeachingJournalAttachment::PHOTO,
                'original_name' => 'Dokumentasi kegiatan kelas.jpg',
            ],
        );
    }

    /**
     * @return array<string, array<int, array{topic: string, activity: string, assignment: ?string, notes: ?string}>>
     */
    private function topics(): array
    {
        return [
            'MTK' => [
                [
                    'topic' => 'Fungsi kuadrat dan grafiknya',
                    'activity' => 'Mengulas bentuk umum fungsi kuadrat, menentukan titik puncak, lalu menggambar grafik bersama di papan tulis.',
                    'assignment' => 'Kerjakan latihan halaman 84 nomor 1–8.',
                    'notes' => 'Sebagian siswa masih perlu bimbingan menentukan sumbu simetri.',
                ],
                [
                    'topic' => 'Sistem persamaan linear dua variabel',
                    'activity' => 'Diskusi kelompok menyelesaikan soal cerita dengan metode eliminasi dan substitusi.',
                    'assignment' => 'LKS halaman 41 nomor 1–5 dikumpulkan pertemuan berikutnya.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Statistika: ukuran pemusatan data',
                    'activity' => 'Menghitung mean, median, dan modus dari data nilai ulangan yang dikumpulkan siswa.',
                    'assignment' => 'Buat tabel distribusi frekuensi dari data yang diberikan.',
                    'notes' => 'Gunakan kalkulator dengan bijak.',
                ],
            ],
            'BIN' => [
                [
                    'topic' => 'Struktur teks argumentasi',
                    'activity' => 'Membaca contoh teks argumentasi, menandai tesis, argumen, dan penegasan ulang.',
                    'assignment' => 'Tulis kerangka teks argumentasi bertema lingkungan sekolah.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Menulis surat resmi',
                    'activity' => 'Menganalisis bagian-bagian surat resmi lalu menyusun surat permohonan narasumber.',
                    'assignment' => 'Revisi surat berdasarkan masukan teman sebangku.',
                    'notes' => 'Perhatikan penggunaan ejaan dan tanda baca.',
                ],
            ],
            'BIG' => [
                [
                    'topic' => 'Describing people and places',
                    'activity' => 'Latihan mendeskripsikan tokoh sekolah menggunakan adjective dan relative clause.',
                    'assignment' => 'Write a short paragraph describing your favorite place in school.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Reading comprehension: news article',
                    'activity' => 'Membaca artikel pendek, menjawab pertanyaan inferensial, dan mendiskusikan kosakata baru.',
                    'assignment' => 'Complete the vocabulary journal for ten new words.',
                    'notes' => 'Bring your dictionary next meeting.',
                ],
            ],
            'FIS' => [
                [
                    'topic' => 'Hukum Newton tentang gerak',
                    'activity' => 'Percobaan sederhana gaya gesek menggunakan bidang miring dan pencatatan hasil pengamatan.',
                    'assignment' => 'Susun laporan singkat hasil percobaan.',
                    'notes' => 'Alat percobaan dikembalikan ke laboratorium.',
                ],
                [
                    'topic' => 'Energi dan usaha',
                    'activity' => 'Menyelesaikan soal energi kinetik dan potensial melalui diskusi berpasangan.',
                    'assignment' => 'Kerjakan soal evaluasi halaman 57 nomor 1–6.',
                    'notes' => null,
                ],
            ],
            'KIM' => [
                [
                    'topic' => 'Ikatan ion dan kovalen',
                    'activity' => 'Membandingkan pembentukan ikatan melalui model molekul dan diskusi kelompok.',
                    'assignment' => 'Gambarkan struktur Lewis untuk lima senyawa yang ditentukan.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Larutan asam dan basa',
                    'activity' => 'Praktikum uji pH menggunakan indikator alami dari ekstrak kunyit.',
                    'assignment' => 'Catat hasil pengamatan dalam tabel dan simpulkan.',
                    'notes' => 'Gunakan sarung tangan saat praktikum.',
                ],
            ],
            'BIO' => [
                [
                    'topic' => 'Struktur dan fungsi sel',
                    'activity' => 'Mengamati preparat sel bawang merah dengan mikroskop dan menggambar hasil pengamatan.',
                    'assignment' => 'Beri keterangan bagian-bagian sel pada gambar hasil pengamatan.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Ekosistem dan rantai makanan',
                    'activity' => 'Membuat jaring-jaring makanan dari studi kasus ekosistem sawah.',
                    'assignment' => 'Presentasikan jaring-jaring makanan kelompok minggu depan.',
                    'notes' => 'Bagi tugas presentasi secara adil.',
                ],
            ],
            'SEJ' => [
                [
                    'topic' => 'Pergerakan nasional Indonesia',
                    'activity' => 'Diskusi kronologi organisasi pergerakan dan peran tokoh muda dalam kemerdekaan.',
                    'assignment' => 'Buat garis waktu peristiwa 1908–1945.',
                    'notes' => null,
                ],
                [
                    'topic' => 'Sejarah lokal dan sumber lisan',
                    'activity' => 'Merancang pertanyaan wawancara untuk tokoh masyarakat sekitar sekolah.',
                    'assignment' => 'Kumpulkan tiga sumber lisan beserta ringkasannya.',
                    'notes' => 'Jaga etika saat wawancara.',
                ],
            ],
            'PJOK' => [
                [
                    'topic' => 'Permainan bola voli',
                    'activity' => 'Latihan passing atas dan bawah, dilanjutkan permainan kecil antar kelompok.',
                    'assignment' => null,
                    'notes' => 'Siswa yang berhalangan mengganti dengan tugas menulis aturan permainan.',
                ],
                [
                    'topic' => 'Kebugaran jasmani',
                    'activity' => 'Tes kebugaran ringan: lari 800 meter dan senam peregangan.',
                    'assignment' => 'Catat denyut nadi sebelum dan sesudah latihan.',
                    'notes' => 'Bawa air minum sendiri.',
                ],
            ],
        ];
    }
}
