<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nisn' => $this->nisn,
            'nama' => $this->name,
            'jenis_kelamin' => $this->gender,
            'jenis_kelamin_label' => $this->genderLabel(),
            'tempat_lahir' => $this->birth_place,
            'tanggal_lahir' => $this->birth_date?->format('Y-m-d'),
            'alamat' => $this->address,
            'status' => $this->status,
            'tahun_masuk' => $this->entry_year,
            'inisial' => mb_substr($this->name, 0, 1),
            'foto' => $this->photoUrl(),
            'kelas' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'nama' => $this->classroom->name,
                'tingkat' => $this->classroom->grade_level,
                'tahun_ajaran' => $this->classroom->academic_year,
                'label' => $this->classroom->label(),
                'wali_kelas' => $this->classroom->homeroomTeacher?->name,
            ] : null),
            'wali' => $this->whenLoaded('guardian', fn () => $this->guardian ? [
                'id' => $this->guardian->id,
                'nama' => $this->guardian->name,
                'telepon' => $this->guardian->phone,
                'pekerjaan' => $this->guardian->occupation,
                'alamat' => $this->guardian->address,
            ] : null),
        ];
    }
}
