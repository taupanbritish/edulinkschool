<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use App\Models\Concerns\HasShowcaseMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;
    use HasPhoto;
    use HasShowcaseMedia;

    protected $fillable = [
        'name',
        'description',
        'photo_path',
        'video_path',
        'video_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
