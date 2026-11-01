<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'grade_level', 'academic_year', 'homeroom_teacher_id'];

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function assignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, TeachingAssignment::class);
    }

    public function label(): string
    {
        return "{$this->name} ({$this->academic_year})";
    }
}
