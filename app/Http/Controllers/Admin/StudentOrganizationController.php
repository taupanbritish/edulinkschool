<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentOrganization;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentOrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $organizations = StudentOrganization::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('short_name', 'ilike', "%{$q}%")
                ->orWhere('advisor', 'ilike', "%{$q}%")))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.student-organizations.index', ['organizations' => $organizations]);
    }

    public function create(): View
    {
        return view('admin.student-organizations.form', [
            'organization' => new StudentOrganization(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        StudentOrganization::create($this->withPhoto($request, $this->validated($request), null));

        return redirect()->route('admin.student-organizations.index')->with('success', 'Organisasi siswa berhasil ditambahkan.');
    }

    public function edit(StudentOrganization $studentOrganization): View
    {
        return view('admin.student-organizations.form', ['organization' => $studentOrganization]);
    }

    public function update(Request $request, StudentOrganization $studentOrganization): RedirectResponse
    {
        $studentOrganization->update($this->withPhoto($request, $this->validated($request), $studentOrganization));

        return redirect()->route('admin.student-organizations.index')->with('success', 'Organisasi siswa berhasil diperbarui.');
    }

    public function destroy(StudentOrganization $studentOrganization): RedirectResponse
    {
        Photo::delete($studentOrganization->photo_path);
        $studentOrganization->delete();

        return redirect()->route('admin.student-organizations.index')->with('success', 'Organisasi siswa berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'advisor' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'vision' => ['nullable', 'string', 'max:2000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'programs' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'short_name' => $data['short_name'] ?? null,
            'advisor' => $data['advisor'] ?? null,
            'description' => $data['description'] ?? null,
            'vision' => $data['vision'] ?? null,
            'mission' => $data['mission'] ?? null,
            'programs' => $data['programs'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function withPhoto(Request $request, array $data, ?StudentOrganization $organization): array
    {
        return [
            ...$data,
            'photo_path' => Photo::update(
                $request->file('photo'),
                $organization?->photo_path,
                $request->boolean('remove_photo'),
                'photos/kesiswaan'
            ),
        ];
    }
}
