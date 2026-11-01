<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Photo;
use App\Support\Youtube;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

abstract class ShowcaseController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function routePrefix(): string;

    abstract protected function publicRoute(): string;

    abstract protected function entityLabel(): string;

    abstract protected function pageTitle(): string;

    abstract protected function pageSubtitle(): string;

    abstract protected function photoFolder(): string;

    abstract protected function videoFolder(): string;

    abstract protected function emptyMessage(): string;

    public function index(Request $request): View
    {
        $model = $this->modelClass();

        $items = $model::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where('name', 'ilike', "%{$q}%"))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.showcase.index', [
            'items' => $items,
            'title' => $this->pageTitle(),
            'subtitle' => $this->pageSubtitle(),
            'routePrefix' => $this->routePrefix(),
            'publicRoute' => $this->publicRoute(),
            'entityLabel' => $this->entityLabel(),
            'emptyMessage' => $this->emptyMessage(),
        ]);
    }

    public function create(): View
    {
        $model = $this->modelClass();

        return view('admin.showcase.form', [
            'item' => new $model(['sort_order' => 0, 'is_active' => true]),
            'title' => 'Tambah '.$this->entityLabel(),
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => $this->entityLabel(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $model = $this->modelClass();

        $model::create([
            ...$data,
            'photo_path' => Photo::update($request->file('photo'), null, $request->boolean('remove_photo'), $this->photoFolder()),
            'video_path' => Photo::update($request->file('video'), null, $request->boolean('remove_video'), $this->videoFolder()),
        ]);

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', $this->entityLabel().' berhasil ditambahkan.');
    }

    protected function formView(Model $item): View
    {
        return view('admin.showcase.form', [
            'item' => $item,
            'title' => 'Ubah '.$this->entityLabel(),
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => $this->entityLabel(),
        ]);
    }

    protected function updateItem(Request $request, Model $item): RedirectResponse
    {
        $data = $this->validated($request);

        $item->update([
            ...$data,
            'photo_path' => Photo::update($request->file('photo'), $item->photo_path, $request->boolean('remove_photo'), $this->photoFolder()),
            'video_path' => Photo::update($request->file('video'), $item->video_path, $request->boolean('remove_video'), $this->videoFolder()),
        ]);

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', $this->entityLabel().' berhasil diperbarui.');
    }

    protected function destroyItem(Model $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', $this->entityLabel().' berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:51200'],
            'remove_video' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (filled($data['video_url'] ?? null) && Youtube::embedUrl($data['video_url']) === null) {
            throw ValidationException::withMessages([
                'video_url' => 'Tautan video harus berupa URL YouTube yang valid (youtu.be atau youtube.com).',
            ]);
        }

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
