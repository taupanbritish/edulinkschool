<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournalAttendance extends Model
{
    use HasFactory;

    protected $fillable = ['journal_id', 'student_id', 'status', 'note'];

    public function journal()
    {
        return $this->belongsTo(TeachingJournal::class, 'journal_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function statusLabel(): string
    {
        return Attendance::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
