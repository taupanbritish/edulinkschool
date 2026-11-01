<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountGenerationController extends Controller
{
    private const TYPES = [
        'guru' => [
            'model' => Teacher::class,
            'role' => User::ROLE_GURU,
            'prefix' => 'guru',
            'password' => 'guru123',
            'label' => 'guru',
            'index' => 'admin.teachers.index',
            'identifier' => 'nip',
        ],
        'siswa' => [
            'model' => Student::class,
            'role' => User::ROLE_SISWA,
            'prefix' => 'siswa',
            'password' => 'siswa123',
            'label' => 'siswa',
            'index' => 'admin.students.index',
            'identifier' => 'nis',
        ],
        'wali' => [
            'model' => Guardian::class,
            'role' => User::ROLE_ORANG_TUA,
            'prefix' => 'wali',
            'password' => 'ortu123',
            'label' => 'wali murid',
            'index' => 'admin.guardians.index',
            'identifier' => 'phone',
        ],
    ];

    public function generate(string $type): RedirectResponse
    {
        $config = self::TYPES[$type] ?? abort(404);
        $created = 0;

        DB::transaction(function () use ($config, &$created) {
            $model = $config['model'];
            $records = $model::whereNull('user_id')->orderBy('name')->get();

            foreach ($records as $record) {
                $user = User::create([
                    'name' => $record->name,
                    'email' => $this->uniqueEmail($record, $config),
                    'phone' => $record->phone ?? null,
                    'password' => $config['password'],
                    'role' => $config['role'],
                    'is_active' => true,
                ]);

                $record->update(['user_id' => $user->id]);
                $created++;
            }
        });

        return redirect()->route($config['index'])->with('success', $created > 0
            ? "{$created} akun {$config['label']} berhasil dibuat. Kata sandi awal: {$config['password']}."
            : "Semua {$config['label']} sudah memiliki akun.");
    }

    private function uniqueEmail(mixed $record, array $config): string
    {
        $name = Str::slug(Str::before($record->name, ','));
        $base = $config['prefix'].'.'.($name !== '' ? $name : 'pengguna');

        $identifier = preg_replace('/\D+/', '', (string) ($record->{$config['identifier']} ?? ''));
        if ($identifier !== '') {
            $base .= '.'.substr($identifier, -4);
        }

        $email = $base.'@sekolah.id';
        $counter = 2;

        while (User::where('email', $email)->exists()) {
            $email = $base.$counter.'@sekolah.id';
            $counter++;
        }

        return $email;
    }
}
