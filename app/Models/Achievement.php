<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;
    use HasPhoto;

    public const CATEGORIES = [
        'akademik' => 'Prestasi Akademik',
        'non_akademik' => 'Prestasi Non Akademik',
    ];

    protected $fillable = [
        'category',
        'title',
        'recipient',
        'level',
        'achieved_at',
        'description',
        'photo_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'achieved_at' => 'date',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('achieved_at')->orderBy('sort_order')->orderBy('title');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
