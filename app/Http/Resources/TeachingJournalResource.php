<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeachingJournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attendance = $this->relationLoaded('attendances') ? $this->attendances->first() : null;
        $assignment = $this->teachingAssignment;

        return [
            'id' => $this->id,
            'tanggal' => $this->date?->toDateString(),
            'tanggal_label' => $this->date?->locale('id')->translatedFormat('j F Y'),
            'hari' => $this->dayName(),
            'mulai' => substr((string) $this->starts_at, 0, 5),
            'selesai' => substr((string) $this->ends_at, 0, 5),
            'rentang_waktu' => $this->timeRange(),
            'mapel' => $assignment?->subject ? [
                'id' => $assignment->subject->id,
                'kode' => $assignment->subject->code,
                'nama' => $assignment->subject->name,
            ] : null,
            'guru' => $assignment?->teacher?->name,
            'guru_foto' => $assignment?->teacher?->photoUrl(),
            'kelas' => $assignment?->classroom?->name,
            'ruang' => $this->schedule?->room,
            'materi' => $this->topic,
            'kegiatan' => $this->activity,
            'tugas' => $this->assignment,
            'catatan' => $this->notes,
            'kehadiran_saya' => $attendance ? [
                'status' => $attendance->status,
                'label' => $attendance->statusLabel(),
            ] : null,
            'lampiran' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'tipe' => $attachment->type,
                'nama' => $attachment->original_name ?: basename($attachment->path),
                'url' => $attachment->url(),
            ])->values()),
        ];
    }
}
