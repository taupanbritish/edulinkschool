<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(Request $request): View
    {
        $achievements = Achievement::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub
                ->where('title', 'ilike', "%{$q}%")
                ->orWhere('recipient', 'ilike', "%{$q}%")))
            ->when(array_key_exists((string) $request->query('category'), Achievement::CATEGORIES), fn ($query) => $query->where('category', $request->query('category')))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.achievements.index', ['achievements' => $achievements]);
    }

    public function create(): View
    {
        return view('admin.achievements.form', [
            'achievement' => new Achievement(['category' => 'akademik', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Achievement::create($this->withPhoto($request, $this->validated($request), null));

        return redirect()->route('admin.achievements.index')->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function edit(Achievement $achievement): View
    {
        return view('admin.achievements.form', ['achievement' => $achievement]);
    }

    public function update(Request $request, Achievement $achievement): RedirectResponse
    {
        $achievement->update($this->withPhoto($request, $this->validated($request), $achievement));

        return redirect()->route('admin.achievements.index')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function destroy(Achievement $achievement): RedirectResponse
    {
        Photo::delete($achievement->photo_path);
        $achievement->delete();

        return redirect()->route('admin.achievements.index')->with('success', 'Prestasi berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category' => ['required', 'in:akademik,non_akademik'],
            'title' => ['required', 'string', 'max:200'],
            'recipient' => ['nullable', 'string', 'max:200'],
            'level' => ['nullable', 'string', 'max:100'],
            'achieved_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'category' => $data['category'],
            'title' => $data['title'],
            'recipient' => $data['recipient'] ?? null,
            'level' => $data['level'] ?? null,
            'achieved_at' => $data['achieved_at'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function withPhoto(Request $request, array $data, ?Achievement $achievement): array
    {
        return [
            ...$data,
            'photo_path' => Photo::update(
                $request->file('photo'),
                $achievement?->photo_path,
                $request->boolean('remove_photo'),
                'photos/prestasi'
            ),
        ];
    }
}
