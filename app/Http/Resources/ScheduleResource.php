<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $start = substr((string) $this->starts_at, 0, 5);
        $end = substr((string) $this->ends_at, 0, 5);

        return [
            'id' => $this->id,
            'hari' => $this->day,
            'nama_hari' => $this->dayName(),
            'mulai' => $start,
            'selesai' => $end,
            'rentang_waktu' => $start.' – '.$end,
            'ruang' => $this->room,
            'mapel' => $this->whenLoaded('assignment', fn () => $this->assignment?->subject ? [
                'id' => $this->assignment->subject->id,
                'kode' => $this->assignment->subject->code,
                'nama' => $this->assignment->subject->name,
            ] : null),
            'guru' => $this->whenLoaded('assignment', fn () => $this->assignment?->teacher?->name),
            'guru_foto' => $this->whenLoaded('assignment', fn () => $this->assignment?->teacher?->photoUrl()),
            'kelas' => $this->whenLoaded('assignment', fn () => $this->assignment?->classroom?->name),
        ];
    }
}
