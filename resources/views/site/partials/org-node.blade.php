@php($member = $node['member'])
<div class="org-node">
    <div class="org-card">
        @if($member->photoUrl())
            <img src="{{ $member->photoUrl() }}" alt="Foto {{ $member->name }}" class="org-photo">
        @else
            <div class="org-photo org-initial">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
        @endif
        <strong>{{ $member->name }}</strong>
        <span>{{ $member->position }}</span>
    </div>

    @if($node['children'] !== [])
        <div class="org-children">
            @foreach($node['children'] as $child)
                @include('site.partials.org-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
