<?php

namespace App\Models;

use App\Support\Media;
use App\Support\Photo;
use App\Support\Youtube;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementAttachment extends Model
{
    public const TYPES = [
        'foto' => 'Foto',
        'video' => 'Video',
    ];

    protected $fillable = [
        'type',
        'path',
        'url',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn (self $attachment) => Photo::delete($attachment->path));
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function isPhoto(): bool
    {
        return $this->type === 'foto';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function mediaUrl(): ?string
    {
        return filled($this->path) ? Media::url($this->path) : $this->url;
    }

    public function embedUrl(): ?string
    {
        return $this->isVideo() && filled($this->url) ? Youtube::embedUrl($this->url) : null;
    }

    public function filename(): ?string
    {
        return filled($this->path) ? basename($this->path) : null;
    }
}
