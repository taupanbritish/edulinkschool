<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Support\SpreadsheetReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentImporter
{
    use NormalizesRows;

    public const COLUMNS = [
        'nis' => 'Wajib. Nomor induk siswa, unik.',
        'nisn' => 'Opsional. Nomor induk siswa nasional, unik.',
        'nama' => 'Wajib. Nama lengkap siswa.',
        'jenis_kelamin' => 'Wajib. Isi L atau P.',
        'tempat_lahir' => 'Opsional.',
        'tanggal_lahir' => 'Opsional. Format YYYY-MM-DD.',
        'kelas' => 'Wajib. Nama kelas, contoh: X IPA 1.',
        'alamat' => 'Opsional.',
        'status' => 'Opsional. aktif, lulus, atau pindah.',
        'tahun_masuk' => 'Opsional. Default tahun berjalan.',
        'email' => 'Opsional. Membuat akun login aplikasi siswa.',
        'password' => 'Opsional. Minimal 6 karakter, default siswa123.',
    ];

    public const REQUIRED = ['nis', 'nama', 'jenis_kelamin', 'kelas'];

    public static function example(): array
    {
        return [
            'nis' => '2026001',
            'nisn' => '0071234567',
            'nama' => 'Ahmad Fauzan',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2011-05-14',
            'kelas' => 'X IPA 1',
            'alamat' => 'Jl. Merdeka No. 10',
            'status' => 'aktif',
            'tahun_masuk' => (string) now()->year,
            'email' => 'ahmad.fauzan@sekolah.id',
            'password' => '',
        ];
    }

    public function import(array $rows): array
    {
        $imported = 0;
        $errors = [];

        $classrooms = Classroom::all()->keyBy(fn (Classroom $classroom) => Str::lower(trim($classroom->name)));
        $seenNis = [];
        $seenNisn = [];
        $seenEmail = [];

        foreach ($rows as $row) {
            $number = $row['number'];
            $data = $row['data'];

            $nis = $this->text($data['nis'] ?? '');
            $nisn = $this->text($data['nisn'] ?? '');
            $name = $this->text($data['nama'] ?? '');
            $gender = $this->gender($data['jenis_kelamin'] ?? '');
            $classroomKey = Str::lower($this->text($data['kelas'] ?? ''));
            $status = Str::lower($this->text($data['status'] ?? '')) ?: 'aktif';
            $email = Str::lower($this->text($data['email'] ?? ''));
            $password = $this->text($data['password'] ?? '');
            $entryYear = $this->text($data['tahun_masuk'] ?? '');
            $birthDateRaw = $data['tanggal_lahir'] ?? null;

            if ($nis === '') {
                $errors[] = "Baris {$number}: NIS wajib diisi.";

                continue;
            }
            if (isset($seenNis[$nis]) || Student::where('nis', $nis)->exists()) {
                $errors[] = "Baris {$number}: NIS {$nis} sudah digunakan.";

                continue;
            }
            if ($name === '') {
                $errors[] = "Baris {$number}: nama wajib diisi.";

                continue;
            }
            if ($gender === '') {
                $errors[] = "Baris {$number}: jenis kelamin harus L atau P.";

                continue;
            }
            if (! $classrooms->has($classroomKey)) {
                $errors[] = "Baris {$number}: kelas \"{$this->text($data['kelas'] ?? '')}\" tidak ditemukan.";

                continue;
            }
            if (! in_array($status, ['aktif', 'lulus', 'pindah'], true)) {
                $errors[] = "Baris {$number}: status harus aktif, lulus, atau pindah.";

                continue;
            }
            if ($nisn !== '' && (isset($seenNisn[$nisn]) || Student::where('nisn', $nisn)->exists())) {
                $errors[] = "Baris {$number}: NISN {$nisn} sudah digunakan.";

                continue;
            }
            if ($email !== '' && (isset($seenEmail[$email]) || User::where('email', $email)->exists())) {
                $errors[] = "Baris {$number}: email {$email} sudah digunakan.";

                continue;
            }
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$number}: format email tidak valid.";

                continue;
            }
            if ($password !== '' && mb_strlen($password) < 6) {
                $errors[] = "Baris {$number}: kata sandi minimal 6 karakter.";

                continue;
            }

            $birthDate = SpreadsheetReader::date($birthDateRaw);
            if (filled($birthDateRaw) && $birthDate === null) {
                $errors[] = "Baris {$number}: tanggal lahir tidak dikenali.";

                continue;
            }

            $year = now()->year;
            if ($entryYear !== '') {
                if (! ctype_digit($entryYear) || (int) $entryYear < 2000 || (int) $entryYear > 2100) {
                    $errors[] = "Baris {$number}: tahun masuk harus angka 2000–2100.";

                    continue;
                }
                $year = (int) $entryYear;
            }

            try {
                DB::transaction(function () use ($nis, $nisn, $name, $gender, $data, $classroomKey, $classrooms, $status, $email, $password, $year, $birthDate) {
                    $user = $email === '' ? null : User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password !== '' ? $password : 'siswa123',
                        'role' => User::ROLE_SISWA,
                    ]);

                    Student::create([
                        'user_id' => $user?->id,
                        'nis' => $nis,
                        'nisn' => $nisn !== '' ? $nisn : null,
                        'name' => $name,
                        'gender' => $gender,
                        'birth_place' => $this->text($data['tempat_lahir'] ?? '') ?: null,
                        'birth_date' => $birthDate,
                        'classroom_id' => $classrooms->get($classroomKey)->id,
                        'address' => $this->text($data['alamat'] ?? '') ?: null,
                        'status' => $status,
                        'entry_year' => $year,
                    ]);
                });
            } catch (\Throwable $exception) {
                $errors[] = "Baris {$number}: gagal disimpan ({$exception->getMessage()}).";

                continue;
            }

            $seenNis[$nis] = true;
            if ($nisn !== '') {
                $seenNisn[$nisn] = true;
            }
            if ($email !== '') {
                $seenEmail[$email] = true;
            }
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
