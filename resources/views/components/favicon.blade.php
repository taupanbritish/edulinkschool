@php($favicon = \App\Support\Media::url(\App\Support\School::get('favicon_path')))

@if($favicon)
    <link rel="icon" href="{{ $favicon }}">
@else
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22%230b2740%22/><text x=%2250%22 y=%2266%22 font-size=%2242%22 font-family=%22Arial%22 font-weight=%22bold%22 fill=%22%23f59e0b%22 text-anchor=%22middle%22>N1</text></svg>">
@endif
