<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PanelUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::whereIn('role', array_keys(User::PANEL_ROLES))
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('email', 'ilike', "%{$q}%")))
            ->orderByRaw("case role when 'admin' then 0 when 'petugas' then 1 else 2 end")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roleLabels' => User::PANEL_ROLES,
            'adminCount' => User::where('role', User::ROLE_ADMIN)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => User::ROLE_PETUGAS, 'is_active' => true]),
            'roleLabels' => User::PANEL_ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.users.index')->with('success', 'Akun panel berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->ensurePanelUser($user);

        return view('admin.users.form', [
            'user' => $user,
            'roleLabels' => User::PANEL_ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensurePanelUser($user);

        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && ($data['role'] !== User::ROLE_ADMIN || ! $request->boolean('is_active'))) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat menurunkan atau menonaktifkan akun sendiri.',
            ]);
        }

        if ($user->isAdmin() && $data['role'] !== User::ROLE_ADMIN && $this->isLastAdmin($user)) {
            throw ValidationException::withMessages([
                'role' => 'Administrator terakhir tidak dapat diubah menjadi peran lain.',
            ]);
        }

        if ($user->isAdmin() && ! $request->boolean('is_active') && $this->isLastAdmin($user)) {
            throw ValidationException::withMessages([
                'is_active' => 'Administrator terakhir tidak dapat dinonaktifkan.',
            ]);
        }

        $user->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.users.index')->with('success', 'Akun panel berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensurePanelUser($user);

        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            return back()->with('error', 'Administrator terakhir tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun panel berhasil dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(User::PANEL_ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    private function ensurePanelUser(User $user): void
    {
        abort_unless($user->isPanelUser(), 404);
    }

    private function isLastAdmin(User $user): bool
    {
        return ! User::where('role', User::ROLE_ADMIN)->whereKeyNot($user->id)->exists();
    }
}
