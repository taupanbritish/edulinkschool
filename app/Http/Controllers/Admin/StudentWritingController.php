<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentWriting;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentWritingController extends Controller
{
    public function index(Request $request): View
    {
        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';

        $writings = StudentWriting::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.mb_strtolower($search).'%';
                $query->where(fn ($query) => $query
                    ->whereRaw('LOWER(title) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(author_name) LIKE ?', [$like]));
            })
            ->when(in_array($request->query('category'), array_keys(StudentWriting::CATEGORIES), true), fn ($query) => $query->where('category', $request->query('category')))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.writings.index', ['writings' => $writings]);
    }

    public function create(): View
    {
        return view('admin.writings.form', [
            'writing' => new StudentWriting([
                'category' => 'artikel',
                'author_type' => 'siswa',
                'is_published' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['photo_path'] = Photo::update($request->file('photo'), null, false, 'photos/majalah');

        StudentWriting::create($data);

        return redirect()->route('admin.writings.index')->with('success', 'Artikel siswa berhasil dibuat.');
    }

    public function edit(StudentWriting $writing): View
    {
        return view('admin.writings.form', ['writing' => $writing]);
    }

    public function update(Request $request, StudentWriting $writing): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['title'] !== $writing->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $writing->id);
        }

        $data['photo_path'] = Photo::update(
            $request->file('photo'),
            $writing->photo_path,
            $request->boolean('remove_photo'),
            'photos/majalah'
        );

        $writing->update($data);

        return redirect()->route('admin.writings.index')->with('success', 'Artikel siswa berhasil diperbarui.');
    }

    public function destroy(StudentWriting $writing): RedirectResponse
    {
        Photo::delete($writing->photo_path);

        $writing->delete();

        return redirect()->route('admin.writings.index')->with('success', 'Artikel siswa berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'author_name' => ['required', 'string', 'max:150'],
            'author_type' => ['required', 'in:siswa,guru'],
            'category' => ['required', 'in:artikel,cerpen,opini'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        return [
            'title' => $data['title'],
            'author_name' => $data['author_name'],
            'author_type' => $data['author_type'],
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->filled('published_at') ? $request->date('published_at') : null,
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while (StudentWriting::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
