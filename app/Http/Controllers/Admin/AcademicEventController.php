<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicEventController extends Controller
{
    public function index(Request $request): View
    {
        $events = AcademicEvent::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'ilike', "%{$q}%"))
            ->when(array_key_exists((string) $request->query('category'), AcademicEvent::CATEGORIES), fn ($query) => $query->where('category', $request->query('category')))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.academic-events.index', ['events' => $events]);
    }

    public function create(): View
    {
        return view('admin.academic-events.form', [
            'event' => new AcademicEvent(['category' => 'kegiatan', 'is_active' => true, 'starts_at' => now()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AcademicEvent::create($this->validated($request));

        return redirect()->route('admin.academic-events.index')->with('success', 'Agenda akademik berhasil ditambahkan.');
    }

    public function edit(AcademicEvent $academicEvent): View
    {
        return view('admin.academic-events.form', ['event' => $academicEvent]);
    }

    public function update(Request $request, AcademicEvent $academicEvent): RedirectResponse
    {
        $academicEvent->update($this->validated($request));

        return redirect()->route('admin.academic-events.index')->with('success', 'Agenda akademik berhasil diperbarui.');
    }

    public function destroy(AcademicEvent $academicEvent): RedirectResponse
    {
        $academicEvent->delete();

        return redirect()->route('admin.academic-events.index')->with('success', 'Agenda akademik berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:kegiatan,ujian,libur,agenda'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'title' => $data['title'],
            'category' => $data['category'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
