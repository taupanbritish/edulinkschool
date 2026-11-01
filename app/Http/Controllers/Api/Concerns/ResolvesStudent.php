<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ResolvesStudent
{
    /**
     * @return Collection<int, Student>
     */
    protected function linkedStudents(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        if ($user->isSiswa()) {
            return Student::with('classroom')
                ->where('user_id', $user->id)
                ->get();
        }

        if ($user->isOrangTua()) {
            return Student::with('classroom')
                ->whereHas('guardian', fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    protected function resolveStudent(Request $request): Student
    {
        $students = $this->linkedStudents($request->user());

        abort_if($students->isEmpty(), 404, 'Data siswa belum ditautkan ke akun ini. Hubungi administrator sekolah.');

        $requested = $request->integer('student_id');

        if ($requested > 0) {
            $student = $students->firstWhere('id', $requested);

            abort_unless($student, 403, 'Anda tidak memiliki akses ke data siswa tersebut.');
        } else {
            $student = $students->first();
        }

        return $student->loadMissing('classroom.homeroomTeacher', 'guardian');
    }
}
