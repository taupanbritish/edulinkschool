<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Support\Str;

class ClassroomImporter
{
    use NormalizesRows;

    public const COLUMNS = [
        'nama' => 'Wajib. Nama kelas, contoh: X IPA 1.',
        'tingkat' => 'Wajib. Angka 1–12.',
        'tahun_ajaran' => 'Wajib. Format YYYY/YYYY, contoh: 2026/2027.',
        'wali_kelas_nip' => 'Opsional. NIP guru wali kelas.',
    ];

    public const REQUIRED = ['nama', 'tingkat', 'tahun_ajaran'];

    public static function example(): array
    {
        return [
            'nama' => 'X IPA 4',
            'tingkat' => '10',
            'tahun_ajaran' => '2026/2027',
            'wali_kelas_nip' => '198501012010011001',
        ];
    }

    public function import(array $rows): array
    {
        $imported = 0;
        $errors = [];
        $seen = [];

        $teachers = Teacher::all()->keyBy(fn (Teacher $teacher) => Str::lower(trim($teacher->nip)));

        foreach ($rows as $row) {
            $number = $row['number'];
            $data = $row['data'];

            $name = $this->text($data['nama'] ?? '');
            $grade = $this->text($data['tingkat'] ?? '');
            $year = $this->text($data['tahun_ajaran'] ?? '');
            $teacherKey = Str::lower($this->text($data['wali_kelas_nip'] ?? ''));

            if ($name === '') {
                $errors[] = "Baris {$number}: nama kelas wajib diisi.";

                continue;
            }
            if (! ctype_digit($grade) || (int) $grade < 1 || (int) $grade > 12) {
                $errors[] = "Baris {$number}: tingkat harus angka 1–12.";

                continue;
            }
            if (! preg_match('/^\d{4}\/\d{4}$/', $year)) {
                $errors[] = "Baris {$number}: tahun ajaran harus berformat YYYY/YYYY.";

                continue;
            }
            if ($teacherKey !== '' && ! $teachers->has($teacherKey)) {
                $errors[] = "Baris {$number}: NIP wali kelas {$this->text($data['wali_kelas_nip'] ?? '')} tidak ditemukan.";

                continue;
            }

            $key = Str::lower($name).'|'.$year;
            if (isset($seen[$key]) || Classroom::where('name', $name)->where('academic_year', $year)->exists()) {
                $errors[] = "Baris {$number}: kelas {$name} tahun ajaran {$year} sudah ada.";

                continue;
            }

            Classroom::create([
                'name' => $name,
                'grade_level' => (int) $grade,
                'academic_year' => $year,
                'homeroom_teacher_id' => $teacherKey !== '' ? $teachers->get($teacherKey)->id : null,
            ]);

            $seen[$key] = true;
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
