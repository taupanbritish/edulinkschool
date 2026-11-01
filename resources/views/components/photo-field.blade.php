@props(['model' => null, 'label' => 'Foto'])

<div class="field full">
    <label for="photo">{{ $label }}</label>
    <div class="photo-field">
        @if($model?->photoUrl())
            <img src="{{ $model->photoUrl() }}" alt="Foto {{ $model->name }}" class="photo-preview">
        @else
            <div class="photo-preview photo-preview-empty"><x-icon name="image" size="24px" /></div>
        @endif
        <div class="photo-controls">
            <input id="photo" type="file" name="photo" class="input" accept="image/jpeg,image/png,image/webp">
            <span class="hint">JPG, PNG, atau WEBP. Maksimal 2 MB.</span>
            @if($model?->photoUrl())
                <label class="checkbox"><input type="checkbox" name="remove_photo" value="1"> Hapus foto saat ini</label>
            @endif
            @error('photo')<span class="error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
