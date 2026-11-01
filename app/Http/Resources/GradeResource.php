<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jenis' => $this->type,
            'jenis_label' => $this->typeLabel(),
            'nilai' => (float) $this->score,
            'catatan' => $this->note,
            'tanggal' => $this->recorded_at?->format('Y-m-d'),
            'tanggal_label' => $this->recorded_at?->locale('id')->translatedFormat('j F Y'),
            'mapel' => $this->whenLoaded('assignment', fn () => $this->assignment?->subject ? [
                'id' => $this->assignment->subject->id,
                'kode' => $this->assignment->subject->code,
                'nama' => $this->assignment->subject->name,
            ] : null),
            'guru' => $this->whenLoaded('assignment', fn () => $this->assignment?->teacher?->name),
            'guru_foto' => $this->whenLoaded('assignment', fn () => $this->assignment?->teacher?->photoUrl()),
        ];
    }
}
