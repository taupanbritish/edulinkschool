<?php

namespace App\Imports;

use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectImporter
{
    use NormalizesRows;

    public const COLUMNS = [
        'kode' => 'Wajib. Kode mata pelajaran, unik, contoh: MTK.',
        'nama' => 'Wajib. Nama mata pelajaran.',
        'deskripsi' => 'Opsional. Keterangan singkat.',
    ];

    public const REQUIRED = ['kode', 'nama'];

    public static function example(): array
    {
        return [
            'kode' => 'MTK',
            'nama' => 'Matematika',
            'deskripsi' => 'Matematika wajib tingkat X–XII.',
        ];
    }

    public function import(array $rows): array
    {
        $imported = 0;
        $errors = [];
        $seen = [];

        foreach ($rows as $row) {
            $number = $row['number'];
            $data = $row['data'];

            $code = Str::upper($this->text($data['kode'] ?? ''));
            $name = $this->text($data['nama'] ?? '');

            if ($code === '') {
                $errors[] = "Baris {$number}: kode mapel wajib diisi.";

                continue;
            }
            if ($name === '') {
                $errors[] = "Baris {$number}: nama mapel wajib diisi.";

                continue;
            }

            $key = Str::lower($code);
            if (isset($seen[$key]) || Subject::where('code', $code)->exists()) {
                $errors[] = "Baris {$number}: kode mapel {$code} sudah digunakan.";

                continue;
            }

            Subject::create([
                'code' => $code,
                'name' => $name,
                'description' => $this->text($data['deskripsi'] ?? '') ?: null,
            ]);

            $seen[$key] = true;
            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
