<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentOrganization extends Model
{
    use HasFactory;
    use HasPhoto;

    protected $fillable = [
        'name',
        'short_name',
        'description',
        'advisor',
        'vision',
        'mission',
        'programs',
        'photo_path',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function missionList(): array
    {
        return $this->lineList('mission');
    }

    public function programList(): array
    {
        return $this->lineList('programs');
    }

    private function lineList(string $attribute): array
    {
        return collect(preg_split('/\R/', (string) $this->{$attribute}))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }
}
