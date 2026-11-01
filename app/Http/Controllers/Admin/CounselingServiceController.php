<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CounselingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounselingServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = CounselingService::query()
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'ilike', "%{$q}%"))
            ->when(array_key_exists((string) $request->query('category'), CounselingService::CATEGORIES), fn ($query) => $query->where('category', $request->query('category')))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.counseling-services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('admin.counseling-services.form', [
            'service' => new CounselingService(['category' => 'layanan', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CounselingService::create($this->validated($request));

        return redirect()->route('admin.counseling-services.index')->with('success', 'Layanan bimbingan konseling berhasil ditambahkan.');
    }

    public function edit(CounselingService $counselingService): View
    {
        return view('admin.counseling-services.form', ['service' => $counselingService]);
    }

    public function update(Request $request, CounselingService $counselingService): RedirectResponse
    {
        $counselingService->update($this->validated($request));

        return redirect()->route('admin.counseling-services.index')->with('success', 'Layanan bimbingan konseling berhasil diperbarui.');
    }

    public function destroy(CounselingService $counselingService): RedirectResponse
    {
        $counselingService->delete();

        return redirect()->route('admin.counseling-services.index')->with('success', 'Layanan bimbingan konseling berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:layanan,program'],
            'description' => ['nullable', 'string', 'max:3000'],
            'schedule' => ['nullable', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'schedule' => $data['schedule'] ?? null,
            'contact' => $data['contact'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
