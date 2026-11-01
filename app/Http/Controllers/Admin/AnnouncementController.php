<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\Youtube;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    private const MAX_VIDEO_URLS = 6;

    public function index(Request $request): View
    {
        $announcements = Announcement::with('author')
            ->withCount('attachments')
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'ilike', "%{$q}%"))
            ->when(in_array($request->query('category'), ['berita', 'pengumuman'], true), fn ($query) => $query->where('category', $request->query('category')))
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.announcements.index', ['announcements' => $announcements]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', [
            'announcement' => new Announcement(['category' => 'berita', 'is_published' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $gallery = $this->galleryInput($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);

        DB::transaction(function () use ($data, $gallery) {
            $this->saveGallery(Announcement::create($data), $gallery);
        });

        return redirect()->route('admin.announcements.index')->with('success', 'Berita/pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', [
            'announcement' => $announcement->load('attachments'),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request);
        $gallery = $this->galleryInput($request);

        if ($data['title'] !== $announcement->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $announcement->id);
        }

        DB::transaction(function () use ($request, $announcement, $data, $gallery) {
            $announcement->update($data);

            $captions = (array) $request->input('captions', []);
            $removals = array_map('intval', (array) $request->input('remove_attachments', []));

            foreach ($announcement->attachments()->get() as $attachment) {
                if (in_array($attachment->id, $removals, true)) {
                    $attachment->delete();

                    continue;
                }

                if (array_key_exists($attachment->id, $captions)) {
                    $caption = trim((string) $captions[$attachment->id]);
                    $attachment->update(['caption' => $caption !== '' ? $caption : null]);
                }
            }

            $this->saveGallery($announcement, $gallery);
        });

        return redirect()->route('admin.announcements.index')->with('success', 'Berita/pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Berita/pengumuman berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:berita,pengumuman'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'videos' => ['nullable', 'array', 'max:4'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/webm', 'max:51200'],
            'video_urls' => ['nullable', 'string', 'max:3000'],
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:150'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['integer'],
        ]);

        return [
            'title' => $data['title'],
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->filled('published_at') ? $request->date('published_at') : null,
        ];
    }

    private function galleryInput(Request $request): array
    {
        $urls = collect(preg_split('/\R/', (string) $request->input('video_urls')))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        if ($urls->count() > self::MAX_VIDEO_URLS) {
            throw ValidationException::withMessages([
                'video_urls' => 'Maksimal '.self::MAX_VIDEO_URLS.' tautan video sekali simpan.',
            ]);
        }

        if ($urls->contains(fn (string $url) => Youtube::embedUrl($url) === null)) {
            throw ValidationException::withMessages([
                'video_urls' => 'Tautan video harus berupa URL YouTube yang valid (youtu.be atau youtube.com).',
            ]);
        }

        return [
            'photos' => $request->file('photos', []),
            'videos' => $request->file('videos', []),
            'urls' => $urls->all(),
        ];
    }

    private function saveGallery(Announcement $announcement, array $gallery): void
    {
        $order = (int) $announcement->attachments()->max('sort_order');

        foreach ($gallery['photos'] as $photo) {
            $announcement->attachments()->create([
                'type' => 'foto',
                'path' => $photo->store('photos/berita', 'public'),
                'sort_order' => ++$order,
            ]);
        }

        foreach ($gallery['videos'] as $video) {
            $announcement->attachments()->create([
                'type' => 'video',
                'path' => $video->store('videos/berita', 'public'),
                'sort_order' => ++$order,
            ]);
        }

        foreach ($gallery['urls'] as $url) {
            $announcement->attachments()->create([
                'type' => 'video',
                'url' => $url,
                'sort_order' => ++$order,
            ]);
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (Announcement::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
