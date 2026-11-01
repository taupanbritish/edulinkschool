<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('kategori');
        $search = $request->query('q');

        $news = Announcement::published()
            ->with('photos')
            ->when(in_array($category, ['berita', 'pengumuman'], true), fn ($query) => $query->where('category', $category))
            ->when($search, fn ($query) => $query->where('title', 'ilike', "%{$search}%"))
            ->latest('published_at')
            ->paginate(6)
            ->withQueryString();

        return view('site.news', [
            'news' => $news,
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->is_published && $announcement->published_at?->isPast(), 404);

        return view('site.news-show', [
            'article' => $announcement->load('attachments'),
            'related' => Announcement::published()
                ->where('id', '!=', $announcement->id)
                ->where('category', $announcement->category)
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
