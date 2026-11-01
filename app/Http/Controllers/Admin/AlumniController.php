<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniDestination;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlumniController extends Controller
{
    public function index(Request $request): View
    {
        $alumni = AlumniDestination::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('university', 'ilike', "%{$q}%")
                ->orWhere('major', 'ilike', "%{$q}%")))
            ->when(array_key_exists((string) $request->query('type'), AlumniDestination::TYPES), fn ($query) => $query->where('type', $request->query('type')))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.alumni.index', ['alumni' => $alumni]);
    }

    public function create(): View
    {
        return view('admin.alumni.form', [
            'alumni' => new AlumniDestination(['type' => 'ptn', 'is_active' => true, 'graduation_year' => now()->year]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AlumniDestination::create($this->withPhoto($request, $this->validated($request), null));

        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil ditambahkan.');
    }

    public function edit(AlumniDestination $alumni): View
    {
        return view('admin.alumni.form', ['alumni' => $alumni]);
    }

    public function update(Request $request, AlumniDestination $alumni): RedirectResponse
    {
        $alumni->update($this->withPhoto($request, $this->validated($request), $alumni));

        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil diperbarui.');
    }

    public function destroy(AlumniDestination $alumni): RedirectResponse
    {
        Photo::delete($alumni->photo_path);
        $alumni->delete();

        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'university' => ['required', 'string', 'max:200'],
            'major' => ['nullable', 'string', 'max:200'],
            'graduation_year' => ['required', 'integer', 'min:1980', 'max:'.(now()->year + 1)],
            'type' => ['required', 'in:ptn,universitas_ternama'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'university' => $data['university'],
            'major' => $data['major'] ?? null,
            'graduation_year' => $data['graduation_year'],
            'type' => $data['type'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function withPhoto(Request $request, array $data, ?AlumniDestination $alumni): array
    {
        return [
            ...$data,
            'photo_path' => Photo::update(
                $request->file('photo'),
                $alumni?->photo_path,
                $request->boolean('remove_photo'),
                'photos/alumni'
            ),
        ];
    }
}
