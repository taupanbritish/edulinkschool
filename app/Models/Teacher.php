<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory, HasPhoto;

    protected $fillable = ['user_id', 'nip', 'name', 'photo_path', 'gender', 'phone'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function assignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, TeachingAssignment::class);
    }

    public function genderLabel(): string
    {
        return $this->gender === 'P' ? 'Perempuan' : 'Laki-laki';
    }
}
