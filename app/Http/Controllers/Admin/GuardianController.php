<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function index(Request $request): View
    {
        $guardians = Guardian::withCount('students')
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('phone', 'ilike', "%{$q}%")))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.guardians.index', [
            'guardians' => $guardians,
            'withoutAccounts' => Guardian::whereNull('user_id')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.guardians.form', ['guardian' => new Guardian]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $this->syncUser($data, null);

        Guardian::create([...$data, 'user_id' => $user?->id]);

        return redirect()->route('admin.guardians.index')->with('success', 'Data wali murid berhasil ditambahkan.');
    }

    public function edit(Guardian $guardian): View
    {
        return view('admin.guardians.form', ['guardian' => $guardian]);
    }

    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $data = $this->validated($request, $guardian);
        $user = $this->syncUser($data, $guardian->user);

        $guardian->update([...$data, 'user_id' => $user?->id ?? $guardian->user_id]);

        return redirect()->route('admin.guardians.index')->with('success', 'Data wali murid berhasil diperbarui.');
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $linked = $guardian->students()->count();

        $guardian->delete();

        $message = "Data wali murid {$guardian->name} berhasil dihapus.";
        if ($linked > 0) {
            $message .= " {$linked} siswa kini tanpa wali murid, tautkan kembali lewat form ubah siswa.";
        }

        return redirect()->route('admin.guardians.index')->with('success', $message);
    }

    private function validated(Request $request, ?Guardian $guardian = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guardian?->user_id)],
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
            'password' => $data['password'] ?? 'ortu123',
            'role' => User::ROLE_ORANG_TUA,
        ]);
    }
}
