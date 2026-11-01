@if(session('success'))
    <div class="flash flash-success">
        <x-icon name="check" />
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="flash flash-error">
        <x-icon name="alert" />
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(session('info'))
    <div class="flash flash-info">
        <x-icon name="info" />
        <span>{{ session('info') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="flash flash-error">
        <x-icon name="alert" />
        <div>
            <strong>Periksa kembali data berikut:</strong>
            <ul style="margin:.35rem 0 0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
