<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::with(['classroom', 'guardian'])
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('nis', 'ilike', "%{$q}%")
                ->orWhere('nisn', 'ilike', "%{$q}%")))
            ->when($request->query('classroom'), fn ($query, $id) => $query->where('classroom_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classrooms' => Classroom::orderBy('name')->get(),
            'withoutAccounts' => Student::whereNull('user_id')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.form', [
            'student' => new Student(['status' => 'aktif', 'entry_year' => now()->year, 'gender' => 'L']),
            'classrooms' => Classroom::orderBy('name')->get(),
            'guardians' => Guardian::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = $this->syncUser($data, null);
        $photo = Photo::update($request->file('photo'), null, $request->boolean('remove_photo'), 'photos/siswa');

        unset($data['photo'], $data['remove_photo']);

        Student::create([
            ...$data,
            'user_id' => $user?->id,
            'photo_path' => $photo,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', [
            'student' => $student,
            'classrooms' => Classroom::orderBy('name')->get(),
            'guardians' => Guardian::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validated($request, $student);

        $user = $this->syncUser($data, $student->user);
        $photo = Photo::update($request->file('photo'), $student->photo_path, $request->boolean('remove_photo'), 'photos/siswa');

        unset($data['photo'], $data['remove_photo']);

        $student->update([
            ...$data,
            'user_id' => $user?->id ?? $student->user_id,
            'photo_path' => $photo,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        Photo::delete($student->photo_path);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    private function validated(Request $request, ?Student $student = null): array
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($student?->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($student?->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:aktif,lulus,pindah'],
            'entry_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        return $data;
    }

    private function syncUser(array $data, ?User $existing): ?User
    {
        if (blank($data['email'] ?? null)) {
            return $existing;
        }

        if ($existing) {
            $existing->update([
                'name' => $data['name'],
                'email' => $data['email'],
                ...(filled($data['password'] ?? null) ? ['password' => $data['password']] : []),
            ]);

            return $existing;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? 'siswa123',
            'role' => User::ROLE_SISWA,
        ]);
    }
}
