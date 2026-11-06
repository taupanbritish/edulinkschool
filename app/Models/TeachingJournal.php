<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_assignment_id',
        'schedule_id',
        'date',
        'starts_at',
        'ends_at',
        'topic',
        'activity',
        'assignment',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(TeachingJournalAttendance::class, 'journal_id');
    }

    public function attachments()
    {
        return $this->hasMany(TeachingJournalAttachment::class, 'journal_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('teachingAssignment', fn (Builder $assignment) => $assignment->where('teacher_id', $teacherId));
    }

    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->whereHas('teachingAssignment', fn (Builder $assignment) => $assignment->where('classroom_id', $classroomId));
    }

    public function dayName(): string
    {
        return $this->date?->locale('id')->translatedFormat('l') ?? '-';
    }

    public function timeRange(): string
    {
        return substr((string) $this->starts_at, 0, 5).' – '.substr((string) $this->ends_at, 0, 5);
    }

    /**
     * @return array<string, int>
     */
    public function attendanceCounts(): array
    {
        $counts = array_fill_keys(array_keys(Attendance::STATUSES), 0);

        foreach ($this->attendances as $attendance) {
            if (array_key_exists($attendance->status, $counts)) {
                $counts[$attendance->status]++;
            }
        }

        return $counts;
    }
}
