<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = Staff::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('nip', 'ilike', "%{$q}%")
                ->orWhere('position', 'ilike', "%{$q}%")))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.staff.index', ['staff' => $staff]);
    }

    public function create(): View
    {
        return view('admin.staff.form', [
            'member' => new Staff(['gender' => 'L', 'position' => 'Staf Tata Usaha', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = Photo::update($request->file('photo'), null, $request->boolean('remove_photo'), 'photos/tata-usaha');

        unset($data['photo'], $data['remove_photo']);

        Staff::create([...$data, 'photo_path' => $photo]);

        return redirect()->route('admin.staff.index')->with('success', 'Data staf tata usaha berhasil ditambahkan.');
    }

    public function edit(Staff $staff): View
    {
        return view('admin.staff.form', ['member' => $staff]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff);
        $photo = Photo::update($request->file('photo'), $staff->photo_path, $request->boolean('remove_photo'), 'photos/tata-usaha');

        unset($data['photo'], $data['remove_photo']);

        $staff->update([...$data, 'photo_path' => $photo]);

        return redirect()->route('admin.staff.index')->with('success', 'Data staf tata usaha berhasil diperbarui.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        Photo::delete($staff->photo_path);
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Data staf tata usaha berhasil dihapus.');
    }

    private function validated(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('staff', 'nip')->ignore($staff?->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);
    }
}
