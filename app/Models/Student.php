<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, HasPhoto;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'name',
        'photo_path',
        'gender',
        'birth_place',
        'birth_date',
        'classroom_id',
        'guardian_id',
        'address',
        'status',
        'entry_year',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedules()
    {
        return Schedule::whereHas('assignment', function ($query) {
            $query->where('classroom_id', $this->classroom_id);
        })->with('assignment.subject', 'assignment.teacher');
    }

    public function genderLabel(): string
    {
        return $this->gender === 'P' ? 'Perempuan' : 'Laki-laki';
    }
}
