<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumGroupController extends Controller
{
    public function index(Request $request): View
    {
        $groups = CurriculumGroup::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where('name', 'ilike', "%{$q}%"))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.curriculum-groups.index', ['groups' => $groups]);
    }

    public function create(): View
    {
        return view('admin.curriculum-groups.form', [
            'group' => new CurriculumGroup(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CurriculumGroup::create($this->validated($request));

        return redirect()->route('admin.curriculum-groups.index')->with('success', 'Kelompok kurikulum berhasil ditambahkan.');
    }

    public function edit(CurriculumGroup $curriculumGroup): View
    {
        return view('admin.curriculum-groups.form', ['group' => $curriculumGroup]);
    }

    public function update(Request $request, CurriculumGroup $curriculumGroup): RedirectResponse
    {
        $curriculumGroup->update($this->validated($request));

        return redirect()->route('admin.curriculum-groups.index')->with('success', 'Kelompok kurikulum berhasil diperbarui.');
    }

    public function destroy(CurriculumGroup $curriculumGroup): RedirectResponse
    {
        $curriculumGroup->delete();

        return redirect()->route('admin.curriculum-groups.index')->with('success', 'Kelompok kurikulum berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'subjects' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'subjects' => $data['subjects'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
