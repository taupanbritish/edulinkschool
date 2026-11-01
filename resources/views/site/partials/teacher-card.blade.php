@php
    $subjects = $teacher->assignments->pluck('subject.name')->filter()->unique()->sort()->values();
@endphp
<div class="teacher-card">
    @if($teacher->photoUrl())
        <img src="{{ $teacher->photoUrl() }}" alt="Foto {{ $teacher->name }}" class="teacher-avatar teacher-photo">
    @else
        <div class="teacher-avatar">{{ strtoupper(substr($teacher->name, 0, 1)) }}</div>
    @endif
    <div>
        <h4>{{ $teacher->name }}</h4>
        <span>NIP {{ $teacher->nip }}</span><br>
        <span>Mengajar: {{ $subjects->isNotEmpty() ? $subjects->join(', ') : 'Guru mata pelajaran' }}</span>
        @if($teacher->classrooms->isNotEmpty())
            <br>
            <span>Wali kelas {{ $teacher->classrooms->pluck('name')->join(', ') }}</span>
        @endif
    </div>
</div>
