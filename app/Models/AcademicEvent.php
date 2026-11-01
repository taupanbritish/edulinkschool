<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicEvent extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'kegiatan' => 'Kegiatan Belajar',
        'ujian' => 'Ujian',
        'libur' => 'Libur',
        'agenda' => 'Agenda Tahunan',
    ];

    protected $fillable = [
        'title',
        'category',
        'starts_at',
        'ends_at',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('starts_at')->orderBy('id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function dateRange(): string
    {
        if (! $this->starts_at) {
            return '-';
        }

        if (! $this->ends_at || $this->ends_at->isSameDay($this->starts_at)) {
            return $this->starts_at->locale('id')->translatedFormat('d F Y');
        }

        return $this->starts_at->locale('id')->translatedFormat('d').'–'.$this->ends_at->locale('id')->translatedFormat('d F Y');
    }
}
