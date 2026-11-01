<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'berita' => 'Berita',
        'pengumuman' => 'Pengumuman',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'category',
        'image_path',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Announcement $announcement) {
            if (blank($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title).'-'.Str::lower(Str::random(5));
            }

            if ($announcement->is_published && blank($announcement->published_at)) {
                $announcement->published_at = now();
            }
        });

        static::deleting(function (Announcement $announcement) {
            $announcement->attachments()->get()->each->delete();
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->attachments()->where('type', 'foto');
    }

    public function videos(): HasMany
    {
        return $this->attachments()->where('type', 'video');
    }

    public function coverPhotoUrl(): ?string
    {
        return $this->photos->first()?->mediaUrl();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function coverStyle(): string
    {
        return $this->category === 'pengumuman'
            ? 'linear-gradient(135deg, var(--teal-600), var(--navy-900))'
            : 'linear-gradient(135deg, var(--navy-800), var(--navy-950))';
    }
}
