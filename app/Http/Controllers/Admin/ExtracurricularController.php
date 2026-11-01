<?php

namespace App\Http\Controllers\Admin;

use App\Models\Extracurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExtracurricularController extends ShowcaseController
{
    public function edit(Extracurricular $extracurricular): View
    {
        return $this->formView($extracurricular);
    }

    public function update(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        return $this->updateItem($request, $extracurricular);
    }

    public function destroy(Extracurricular $extracurricular): RedirectResponse
    {
        return $this->destroyItem($extracurricular);
    }

    protected function modelClass(): string
    {
        return Extracurricular::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.extracurriculars';
    }

    protected function publicRoute(): string
    {
        return 'extracurriculars';
    }

    protected function entityLabel(): string
    {
        return 'Ekstrakurikuler';
    }

    protected function pageTitle(): string
    {
        return 'Ekstrakurikuler';
    }

    protected function pageSubtitle(): string
    {
        return 'Kelola kegiatan pengembangan minat dan bakat siswa beserta gambar dan video.';
    }

    protected function photoFolder(): string
    {
        return 'photos/ekstrakurikuler';
    }

    protected function videoFolder(): string
    {
        return 'videos/ekstrakurikuler';
    }

    protected function emptyMessage(): string
    {
        return 'Belum ada data ekstrakurikuler.';
    }
}
