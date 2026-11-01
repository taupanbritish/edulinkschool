@php
    $logo = \App\Support\Media::url(\App\Support\School::get('logo_path'));
    $label = \App\Support\School::get('school_short', 'Sekolah');
@endphp

<div {{ $attributes->class(['brand-mark']) }}>
    @if($logo)
        <img src="{{ $logo }}" alt="Logo {{ $label }}">
    @else
        N1
    @endif
</div>
