@php($embed = $item->videoEmbedUrl())
@php($videoFile = $item->videoFileUrl())

<article class="showcase-card">
    <div class="showcase-media">
        @if($item->photoUrl())
            <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}" loading="lazy">
        @else
            <div class="showcase-placeholder"><x-icon :name="$placeholderIcon" size="30px" /></div>
        @endif
    </div>
    <div class="showcase-body">
        <h2>{{ $item->name }}</h2>
        @if($item->description)
            <p>{{ $item->description }}</p>
        @endif
        @if($embed)
            <div class="video-frame showcase-video">
                <iframe src="{{ $embed }}" title="Video {{ $item->name }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        @elseif($videoFile)
            <video controls preload="metadata" src="{{ $videoFile }}"></video>
        @endif
    </div>
</article>
