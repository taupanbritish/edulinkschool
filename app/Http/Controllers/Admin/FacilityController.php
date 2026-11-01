<?php

namespace App\Http\Controllers\Admin;

use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends ShowcaseController
{
    public function edit(Facility $facility): View
    {
        return $this->formView($facility);
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        return $this->updateItem($request, $facility);
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        return $this->destroyItem($facility);
    }

    protected function modelClass(): string
    {
        return Facility::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.facilities';
    }

    protected function publicRoute(): string
    {
        return 'facilities';
    }

    protected function entityLabel(): string
    {
        return 'Fasilitas';
    }

    protected function pageTitle(): string
    {
        return 'Fasilitas Sekolah';
    }

    protected function pageSubtitle(): string
    {
        return 'Kelola sarana dan prasarana sekolah beserta gambar dan video.';
    }

    protected function photoFolder(): string
    {
        return 'photos/fasilitas';
    }

    protected function videoFolder(): string
    {
        return 'videos/fasilitas';
    }

    protected function emptyMessage(): string
    {
        return 'Belum ada data fasilitas.';
    }
}
