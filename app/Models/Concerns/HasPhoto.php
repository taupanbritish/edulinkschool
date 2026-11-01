<?php

namespace App\Models\Concerns;

use App\Support\Media;

trait HasPhoto
{
    public function photoUrl(): ?string
    {
        return Media::url($this->photo_path);
    }
}
