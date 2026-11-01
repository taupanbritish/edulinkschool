<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::with('classrooms')
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('nip', 'ilike', "%{$q}%")))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.teachers.index', [
            'teachers' => $teachers,
            'withoutAccounts' => Teacher::whereNull('user_id')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.teachers.form', ['teacher' => new Teacher(['gender' => 'L'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $this->syncUser($data, null);
        $photo = Photo::update($request->file('photo'), null, $request->boolean('remove_photo'), 'photos/guru');

        unset($data['photo'], $data['remove_photo']);

        Teacher::create([...$data, 'user_id' => $user?->id, 'photo_path' => $photo]);

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher): View
    {
        return view('admin.teachers.form', ['teacher' => $teacher]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $this->validated($request, $teacher);
        $user = $this->syncUser($data, $teacher->user);
        $photo = Photo::update($request->file('photo'), $teacher->photo_path, $request->boolean('remove_photo'), 'photos/guru');

        unset($data['photo'], $data['remove_photo']);

        $teacher->update([...$data, 'user_id' => $user?->id ?? $teacher->user_id, 'photo_path' => $photo]);

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        Photo::delete($teacher->photo_path);
        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }

    private function validated(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'nip' => ['required', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacher?->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);
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
                'phone' => $data['phone'] ?? null,
                ...(filled($data['password'] ?? null) ? ['password' => $data['password']] : []),
            ]);

            return $existing;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'] ?? 'guru123',
            'role' => User::ROLE_GURU,
        ]);
    }
}
