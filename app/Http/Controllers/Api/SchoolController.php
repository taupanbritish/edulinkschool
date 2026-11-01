<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Support\Media;
use App\Support\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'nama' => School::get('school_name', 'Sekolah'),
            'nama_singkat' => School::label(),
            'slogan' => School::get('tagline'),
            'npsn' => School::get('npsn'),
            'akreditasi' => School::get('accreditation'),
            'tahun_berdiri' => School::get('founded_year'),
            'alamat' => School::get('address'),
            'telepon' => School::get('phone'),
            'whatsapp' => School::get('whatsapp'),
            'email' => School::get('email'),
            'jam_layanan' => School::get('office_hours'),
            'kepala_sekolah' => School::get('principal_name'),
            'logo' => Media::url(School::get('logo_path')),
            'inisial' => Str::upper(Str::substr(School::label(), 0, 2)),
        ]);
    }

    public function announcements(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['nullable', 'in:berita,pengumuman'],
        ]);

        $query = Announcement::published()
            ->with(['author', 'attachments'])
            ->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return AnnouncementResource::collection($query->paginate(10))->response();
    }

    public function announcement(Announcement $announcement): JsonResponse
    {
        abort_unless(
            $announcement->is_published && $announcement->published_at && $announcement->published_at->isPast(),
            404,
            'Pengumuman tidak ditemukan.'
        );

        return response()->json([
            'data' => new AnnouncementResource($announcement->loadMissing(['author', 'attachments'])),
        ]);
    }
}
