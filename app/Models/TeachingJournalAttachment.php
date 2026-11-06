<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournalAttachment extends Model
{
    use HasFactory;

    public const PHOTO = 'foto';

    public const DOCUMENT = 'dokumen';

    protected $fillable = ['journal_id', 'type', 'path', 'original_name'];

    public function journal()
    {
        return $this->belongsTo(TeachingJournal::class, 'journal_id');
    }

    public function isPhoto(): bool
    {
        return $this->type === self::PHOTO;
    }

    public function url(): ?string
    {
        return Media::url($this->path);
    }
}
