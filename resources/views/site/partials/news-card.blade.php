@php
    $isAnnouncement = $item->category === 'pengumuman';
    $cover = $item->coverPhotoUrl();
@endphp
<article class="news-card">
    <a href="{{ route('news.show', $item) }}" class="news-cover {{ $cover ? 'has-photo' : '' }}" style="background:{{ $item->coverStyle() }}">
        @if($cover)
            <img src="{{ $cover }}" alt="" class="cover-photo" loading="lazy">
        @endif
        <span class="cover-mark">{{ $school['school_short'] ?? 'N1' }}</span>
        <span class="cover-label">{{ $item->categoryLabel() }} · {{ $item->published_at?->locale('id')->translatedFormat('d M Y') }}</span>
    </a>
    <div class="news-body">
        <div class="news-meta">
            <span class="badge {{ $isAnnouncement ? 'badge-announcement' : 'badge-news' }}">{{ $item->categoryLabel() }}</span>
            <span>{{ $item->published_at?->locale('id')->translatedFormat('d M Y') }}</span>
        </div>
        <h3><a href="{{ route('news.show', $item) }}">{{ $item->title }}</a></h3>
        <p>{{ $item->excerpt }}</p>
        <a href="{{ route('news.show', $item) }}" class="read-more">
            Baca selengkapnya <x-icon name="arrow-right" size="14px" />
        </a>
    </div>
</article>
