<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teaching_assignment_id',
        'type',
        'score',
        'note',
        'recorded_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'recorded_at' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assignment()
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'tugas' => 'Tugas',
            'uts' => 'UTS',
            'uas' => 'UAS',
            'proyek' => 'Proyek',
            default => ucfirst($this->type),
        };
    }
}
