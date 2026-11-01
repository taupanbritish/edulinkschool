<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniDestination extends Model
{
    use HasFactory;
    use HasPhoto;

    public const TYPES = [
        'ptn' => 'PTN',
        'universitas_ternama' => 'Universitas Ternama',
    ];

    protected $fillable = [
        'name',
        'photo_path',
        'university',
        'major',
        'graduation_year',
        'type',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
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
        return $query->orderBy('sort_order')->orderByDesc('graduation_year')->orderBy('name');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
