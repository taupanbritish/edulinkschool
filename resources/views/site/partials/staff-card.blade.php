<div class="teacher-card">
    @if($member->photoUrl())
        <img src="{{ $member->photoUrl() }}" alt="Foto {{ $member->name }}" class="teacher-avatar teacher-photo">
    @else
        <div class="teacher-avatar">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
    @endif
    <div>
        <h4>{{ $member->name }}</h4>
        <span>{{ $member->position }}</span><br>
        <span>{{ $member->nip ? 'NIP '.$member->nip : 'Staf Tata Usaha' }}</span>
    </div>
</div>
