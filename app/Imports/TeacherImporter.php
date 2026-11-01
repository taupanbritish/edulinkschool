<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherImporter
{
    use NormalizesRows;

    public const COLUMNS = [
        'nip' => 'Wajib. Nomor induk pegawai, unik.',
        'nama' => 'Wajib. Nama lengkap guru.',
        'jenis_kelamin' => 'Wajib. Isi L atau P.',
        'telepon' => 'Opsional. Nomor telepon / WhatsApp.',
        'email' => 'Opsional. Membuat akun ruang guru.',
        'password' => 'Opsional. Minimal 6 karakter, default guru123.',
    ];

    public const REQUIRED = ['nip', 'nama', 'jenis_kelamin'];

    public static function example(): array
    {
        return [
            'nip' => '198501012010011001',
            'nama' => 'Rina Kartika, S.Pd.',
            'jenis_kelamin' => 'P',
            'telepon' => '081234567890',
            'email' => 'rina.kartika@sekolah.id',
            'password' => '',
        ];
    }

    public function import(array $rows): array
    {
        $imported = 0;
        $errors = [];
        $seenNip = [];
        $seenEmail = [];

        foreach ($rows as $row) {
            $number = $row['number'];
            $data = $row['data'];

            $nip = $this->text($data['nip'] ?? '');
            $name = $this->text($data['nama'] ?? '');
            $gender = $this->gender($data['jenis_kelamin'] ?? '');
            $email = Str::lower($this->text($data['email'] ?? ''));
            $password = $this->text($data['password'] ?? '');

            if ($nip === '') {
                $errors[] = "Baris {$number}: NIP wajib diisi.";

                continue;
            }
            if (isset($seenNip[$nip]) || Teacher::where('nip', $nip)->exists()) {
                $errors[] = "Baris {$number}: NIP {$nip} sudah digunakan.";

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

            $phone = $this->text($data['telepon'] ?? '') ?: null;

            try {
                DB::transaction(function () use ($nip, $name, $gender, $phone, $email, $password) {
                    $user = $email === '' ? null : User::create([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password !== '' ? $password : 'guru123',
                        'role' => User::ROLE_GURU,
                    ]);

                    Teacher::create([
                        'user_id' => $user?->id,
                        'nip' => $nip,
                        'name' => $name,
                        'gender' => $gender,
                        'phone' => $phone,
                    ]);
                });
            } catch (\Throwable $exception) {
                $errors[] = "Baris {$number}: gagal disimpan ({$exception->getMessage()}).";

                continue;
            }

            $seenNip[$nip] = true;
            if ($email !== '') {
                $seenEmail[$email] = true;
            }
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
