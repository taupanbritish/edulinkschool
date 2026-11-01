<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentWriting extends Model
{
    use HasFactory;
    use HasPhoto;

    public const CATEGORIES = [
        'artikel' => 'Artikel',
        'cerpen' => 'Cerpen',
        'opini' => 'Opini',
    ];

    public const AUTHOR_TYPES = [
        'siswa' => 'Siswa',
        'guru' => 'Guru',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'author_name',
        'author_type',
        'category',
        'excerpt',
        'body',
        'photo_path',
        'sort_order',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (StudentWriting $writing) {
            if (blank($writing->slug)) {
                $writing->slug = Str::slug($writing->title);
            }

            if ($writing->is_published && blank($writing->published_at)) {
                $writing->published_at = now();
            }
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function authorTypeLabel(): string
    {
        return self::AUTHOR_TYPES[$this->author_type] ?? ucfirst($this->author_type);
    }
}
