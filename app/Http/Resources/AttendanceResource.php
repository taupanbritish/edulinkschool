<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->date?->format('Y-m-d'),
            'tanggal_label' => $this->date?->locale('id')->translatedFormat('j F Y'),
            'nama_hari' => $this->date?->locale('id')->translatedFormat('l'),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'catatan' => $this->note,
        ];
    }
}
