<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationMemberController extends Controller
{
    public function index(): View
    {
        $members = OrganizationMember::ordered()->get();

        return view('admin.organization.index', [
            'rows' => $this->flatten($members),
            'total' => $members->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.organization.form', [
            'member' => new OrganizationMember,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = Photo::update($request->file('photo'), null, $request->boolean('remove_photo'), 'photos/struktur');

        unset($data['photo'], $data['remove_photo']);

        OrganizationMember::create([...$data, 'photo_path' => $photo]);

        return redirect()->route('admin.organization.index')->with('success', 'Anggota struktur organisasi berhasil ditambahkan.');
    }

    public function edit(OrganizationMember $organization): View
    {
        $excluded = [$organization->id, ...$organization->descendantIds()];

        return view('admin.organization.form', [
            'member' => $organization,
            'parentOptions' => $this->parentOptions($excluded),
        ]);
    }

    public function update(Request $request, OrganizationMember $organization): RedirectResponse
    {
        $excluded = [$organization->id, ...$organization->descendantIds()];
        $data = $this->validated($request, $excluded);
        $photo = Photo::update($request->file('photo'), $organization->photo_path, $request->boolean('remove_photo'), 'photos/struktur');

        unset($data['photo'], $data['remove_photo']);

        $organization->update([...$data, 'photo_path' => $photo]);

        return redirect()->route('admin.organization.index')->with('success', 'Struktur organisasi berhasil diperbarui.');
    }

    public function destroy(OrganizationMember $organization): RedirectResponse
    {
        Photo::delete($organization->photo_path);
        $organization->children()->update(['parent_id' => $organization->parent_id]);
        $organization->delete();

        return redirect()->route('admin.organization.index')->with('success', 'Anggota struktur organisasi berhasil dihapus.');
    }

    private function validated(Request $request, array $excluded = []): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:organization_members,id', Rule::notIn($excluded)],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);
    }

    private function flatten($members, ?int $parentId = null, int $depth = 0, array $rows = []): array
    {
        foreach ($members->whereStrict('parent_id', $parentId) as $member) {
            $rows[] = ['member' => $member, 'depth' => $depth];
            $rows = $this->flatten($members, $member->id, $depth + 1, $rows);
        }

        return $rows;
    }

    private function parentOptions(array $excluded = []): array
    {
        $members = OrganizationMember::ordered()->get();

        if ($excluded !== []) {
            $members = $members->whereNotIn('id', $excluded);
        }

        return $this->flatten($members);
    }
}
