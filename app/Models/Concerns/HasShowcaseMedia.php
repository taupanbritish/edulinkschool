<?php

namespace App\Models\Concerns;

use App\Support\Media;
use App\Support\Photo;
use App\Support\Youtube;

trait HasShowcaseMedia
{
    public static function bootHasShowcaseMedia(): void
    {
        static::deleting(function ($model) {
            Photo::delete($model->photo_path);
            Photo::delete($model->video_path);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function videoEmbedUrl(): ?string
    {
        return Youtube::embedUrl($this->video_url);
    }

    public function videoFileUrl(): ?string
    {
        return Media::url($this->video_path);
    }
}
