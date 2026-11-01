<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->title,
            'slug' => $this->slug,
            'kategori' => $this->category,
            'kategori_label' => $this->categoryLabel(),
            'ringkasan' => $this->excerpt,
            'isi' => $this->body,
            'penulis' => $this->whenLoaded('author', fn () => $this->author?->name),
            'diterbitkan_pada' => $this->published_at?->toIso8601String(),
            'diterbitkan_label' => $this->published_at?->locale('id')->translatedFormat('j F Y'),
            'diterbitkan_singkat' => $this->published_at?->locale('id')->translatedFormat('d M Y'),
            'foto_sampul' => $this->whenLoaded('attachments', fn () => $this->attachments->firstWhere('type', 'foto')?->mediaUrl()),
            'galeri' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'tipe' => $attachment->type,
                'tipe_label' => $attachment->typeLabel(),
                'url' => $attachment->mediaUrl(),
                'semat' => $attachment->embedUrl(),
                'caption' => $attachment->caption,
            ])->values()),
        ];
    }
}
