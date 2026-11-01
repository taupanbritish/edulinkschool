<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\Achievement;
use App\Models\AlumniDestination;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\ContactMessage;
use App\Models\CounselingService;
use App\Models\CurriculumGroup;
use App\Models\Extracurricular;
use App\Models\Facility;
use App\Models\OrganizationMember;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentOrganization;
use App\Models\StudentWriting;
use App\Models\Teacher;
use App\Support\School;
use App\Support\Youtube;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'news' => Announcement::published()->with('photos')->where('category', 'berita')->latest('published_at')->take(3)->get(),
            'announcements' => Announcement::published()->where('category', 'pengumuman')->latest('published_at')->take(4)->get(),
            'youtubeEmbed' => Youtube::embedUrl(School::get('youtube_url')),
            'stats' => [
                'students' => Student::where('status', 'aktif')->count(),
                'teachers' => Teacher::count(),
                'staff' => Staff::where('is_active', true)->count(),
                'classrooms' => Classroom::count(),
            ],
        ]);
    }

    public function profile(): View
    {
        return view('site.profile', [
            'teachers' => Teacher::with(['assignments.subject', 'classrooms'])->orderBy('name')->paginate(4, ['*'], 'guru_page')->fragment('guru')->withQueryString(),
            'staff' => Staff::where('is_active', true)->ordered()->paginate(4, ['*'], 'staf_page')->fragment('tata-usaha')->withQueryString(),
        ]);
    }

    public function teachers(): View
    {
        return view('site.teachers', [
            'teachers' => Teacher::with(['assignments.subject', 'classrooms'])->orderBy('name')->paginate(12)->withQueryString(),
        ]);
    }

    public function contact(): View
    {
        return view('site.contact');
    }

    public function sendContactMessage(Request $request): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('contact')->withFragment('kirim-pesan')
                ->with('success', 'Terima kasih, pesan Anda sudah kami terima.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $data['ip_address'] = $request->ip();

        ContactMessage::create($data);

        return redirect()->route('contact')->withFragment('kirim-pesan')
            ->with('success', 'Pesan Anda sudah terkirim. Tim sekolah akan menindaklanjuti pada hari kerja.');
    }

    public function search(Request $request): View
    {
        $term = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $groups = mb_strlen($term) >= 2 ? $this->searchGroups($term) : [];

        return view('site.search', [
            'term' => $term,
            'groups' => $groups,
            'total' => collect($groups)->sum(fn (array $group) => count($group['items'])),
        ]);
    }

    public function writings(Request $request): View
    {
        $category = $request->query('kategori');
        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';

        $writings = StudentWriting::published()
            ->when(in_array($category, array_keys(StudentWriting::CATEGORIES), true), fn ($query) => $query->where('category', $category))
            ->when($search !== '', fn ($query) => $this->whereAnyColumn($query, ['title', 'author_name', 'excerpt'], $search))
            ->ordered()
            ->paginate(6)
            ->withQueryString();

        return view('site.writings', [
            'writings' => $writings,
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function writing(StudentWriting $studentWriting): View
    {
        abort_unless($studentWriting->is_published && $studentWriting->published_at?->isPast(), 404);

        return view('site.writing-show', [
            'writing' => $studentWriting,
            'related' => StudentWriting::published()
                ->where('id', '!=', $studentWriting->id)
                ->where('category', $studentWriting->category)
                ->ordered()
                ->take(3)
                ->get(),
        ]);
    }

    public function facilities(): View
    {
        return view('site.showcase', [
            'pageTitle' => 'Fasilitas Sekolah',
            'pageDescription' => 'Sarana dan prasarana yang mendukung kegiatan belajar, karya siswa, dan kenyamanan seluruh warga sekolah.',
            'items' => Facility::active()->ordered()->get(),
            'placeholderIcon' => 'school',
            'emptyMessage' => 'Belum ada fasilitas yang dipublikasikan.',
        ]);
    }

    public function extracurriculars(): View
    {
        return view('site.showcase', [
            'pageTitle' => 'Ekstrakurikuler',
            'pageDescription' => 'Wadah pengembangan minat, bakat, dan kepemimpinan siswa di luar jam pelajaran.',
            'items' => Extracurricular::active()->ordered()->get(),
            'placeholderIcon' => 'award',
            'emptyMessage' => 'Belum ada ekstrakurikuler yang dipublikasikan.',
        ]);
    }

    public function studentOrganizations(): View
    {
        return view('site.student-organizations', [
            'organizations' => StudentOrganization::active()->ordered()->get(),
        ]);
    }

    public function counseling(): View
    {
        $services = CounselingService::active()->ordered()->get();

        return view('site.counseling', [
            'services' => $services->where('category', 'layanan')->values(),
            'programs' => $services->where('category', 'program')->values(),
        ]);
    }

    public function academicAchievements(): View
    {
        return $this->achievementPage('akademik');
    }

    public function nonAcademicAchievements(): View
    {
        return $this->achievementPage('non_akademik');
    }

    public function curriculum(): View
    {
        return view('site.curriculum', [
            'curriculumName' => School::get('curriculum_name', 'Kurikulum Merdeka'),
            'curriculumDescription' => School::get('curriculum_description'),
            'groups' => CurriculumGroup::active()->ordered()->get(),
        ]);
    }

    public function academicCalendar(): View
    {
        $events = AcademicEvent::active()->ordered()->get();

        return view('site.academic-calendar', [
            'eventsByMonth' => $events->groupBy(fn (AcademicEvent $event) => $event->starts_at->locale('id')->translatedFormat('F Y')),
            'total' => $events->count(),
            'categories' => AcademicEvent::CATEGORIES,
        ]);
    }

    public function alumni(): View
    {
        $items = AlumniDestination::active()->ordered()->get();

        return view('site.alumni', [
            'items' => $items,
            'total' => $items->count(),
            'ptnCount' => $items->where('type', 'ptn')->count(),
            'universities' => $items->groupBy('university')->map->count()->sortDesc(),
            'latestYear' => $items->max('graduation_year'),
        ]);
    }

    private function achievementPage(string $category): View
    {
        return view('site.achievements', [
            'category' => $category,
            'pageTitle' => Achievement::CATEGORIES[$category],
            'pageDescription' => $category === 'akademik'
                ? 'Capaian akademik siswa pada olimpiade sains, lomba karya ilmiah, dan kompetisi bidang studi.'
                : 'Capaian siswa di bidang seni, olahraga, kepemimpinan, dan kegiatan pengembangan diri.',
            'items' => Achievement::active()->where('category', $category)->ordered()->get(),
        ]);
    }

    public function staff(): View
    {
        return view('site.staff', [
            'staff' => Staff::where('is_active', true)->ordered()->get(),
        ]);
    }

    public function organization(): View
    {
        $members = OrganizationMember::ordered()->get();

        return view('site.organization', [
            'roots' => $this->tree($members),
            'total' => $members->count(),
        ]);
    }

    private function whereAnyColumn($query, array $columns, string $term)
    {
        $like = '%'.mb_strtolower(addcslashes($term, '\\%_')).'%';

        return $query->where(function ($query) use ($columns, $like) {
            foreach ($columns as $column) {
                $query->orWhereRaw("LOWER({$column}) LIKE ?", [$like]);
            }
        });
    }

    private function searchGroups(string $term): array
    {
        $groups = [
            [
                'label' => 'Berita & Pengumuman',
                'icon' => 'megaphone',
                'items' => $this->whereAnyColumn(Announcement::published(), ['title', 'excerpt', 'body'], $term)
                    ->latest('published_at')->take(5)->get()
                    ->map(fn (Announcement $item) => [
                        'title' => $item->title,
                        'meta' => $item->categoryLabel().' · '.$item->published_at?->locale('id')->translatedFormat('d M Y'),
                        'excerpt' => $item->excerpt,
                        'url' => route('news.show', $item),
                    ])->all(),
            ],
            [
                'label' => 'Majalah / Artikel Siswa',
                'icon' => 'book-open',
                'items' => $this->whereAnyColumn(StudentWriting::published(), ['title', 'author_name', 'excerpt', 'body'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (StudentWriting $item) => [
                        'title' => $item->title,
                        'meta' => $item->categoryLabel().' · '.$item->author_name,
                        'excerpt' => $item->excerpt,
                        'url' => route('writings.show', $item),
                    ])->all(),
            ],
            [
                'label' => 'Guru',
                'icon' => 'users',
                'items' => $this->whereAnyColumn(Teacher::query(), ['name', 'nip'], $term)
                    ->orderBy('name')->take(5)->get()
                    ->map(fn (Teacher $item) => [
                        'title' => $item->name,
                        'meta' => 'Guru',
                        'excerpt' => null,
                        'url' => route('teachers'),
                    ])->all(),
            ],
            [
                'label' => 'Tata Usaha',
                'icon' => 'briefcase',
                'items' => $this->whereAnyColumn(Staff::where('is_active', true), ['name', 'position'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (Staff $item) => [
                        'title' => $item->name,
                        'meta' => $item->position,
                        'excerpt' => null,
                        'url' => route('staff'),
                    ])->all(),
            ],
            [
                'label' => 'Fasilitas',
                'icon' => 'school',
                'items' => $this->whereAnyColumn(Facility::active(), ['name', 'description'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (Facility $item) => [
                        'title' => $item->name,
                        'meta' => 'Fasilitas sekolah',
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('facilities'),
                    ])->all(),
            ],
            [
                'label' => 'Ekstrakurikuler',
                'icon' => 'award',
                'items' => $this->whereAnyColumn(Extracurricular::active(), ['name', 'description'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (Extracurricular $item) => [
                        'title' => $item->name,
                        'meta' => 'Ekstrakurikuler',
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('extracurriculars'),
                    ])->all(),
            ],
            [
                'label' => 'Organisasi Siswa',
                'icon' => 'network',
                'items' => $this->whereAnyColumn(StudentOrganization::active(), ['name', 'short_name', 'description', 'advisor'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (StudentOrganization $item) => [
                        'title' => $item->name,
                        'meta' => filled($item->advisor) ? 'Pembina '.$item->advisor : 'Organisasi siswa',
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('student-organizations'),
                    ])->all(),
            ],
            [
                'label' => 'Bimbingan Konseling',
                'icon' => 'message',
                'items' => $this->whereAnyColumn(CounselingService::active(), ['title', 'description'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (CounselingService $item) => [
                        'title' => $item->title,
                        'meta' => $item->categoryLabel(),
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('counseling'),
                    ])->all(),
            ],
            [
                'label' => 'Prestasi',
                'icon' => 'star',
                'items' => $this->whereAnyColumn(Achievement::active(), ['title', 'recipient', 'description'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (Achievement $item) => [
                        'title' => $item->title,
                        'meta' => trim(($item->level ?? '').' · '.$item->categoryLabel(), ' ·'),
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route($item->category === 'non_akademik' ? 'achievements.non_academic' : 'achievements.academic'),
                    ])->all(),
            ],
            [
                'label' => 'Kurikulum',
                'icon' => 'book',
                'items' => $this->whereAnyColumn(CurriculumGroup::active(), ['name', 'description', 'subjects'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (CurriculumGroup $item) => [
                        'title' => $item->name,
                        'meta' => 'Kelompok mata pelajaran',
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('curriculum'),
                    ])->all(),
            ],
            [
                'label' => 'Kalender Akademik',
                'icon' => 'calendar',
                'items' => $this->whereAnyColumn(AcademicEvent::active(), ['title', 'description'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (AcademicEvent $item) => [
                        'title' => $item->title,
                        'meta' => $item->starts_at?->locale('id')->translatedFormat('d M Y') ?? 'Agenda sekolah',
                        'excerpt' => Str::limit((string) $item->description, 140),
                        'url' => route('academic-calendar'),
                    ])->all(),
            ],
            [
                'label' => 'Info PTN / Alumni',
                'icon' => 'graduation-cap',
                'items' => $this->whereAnyColumn(AlumniDestination::active(), ['name', 'university', 'major'], $term)
                    ->ordered()->take(5)->get()
                    ->map(fn (AlumniDestination $item) => [
                        'title' => $item->name,
                        'meta' => $item->university.' · '.$item->major,
                        'excerpt' => null,
                        'url' => route('alumni'),
                    ])->all(),
            ],
        ];

        return array_values(array_filter($groups, fn (array $group) => $group['items'] !== []));
    }

    private function tree($members, ?int $parentId = null): array
    {
        $nodes = [];

        foreach ($members->whereStrict('parent_id', $parentId) as $member) {
            $nodes[] = [
                'member' => $member,
                'children' => $this->tree($members, $member->id),
            ];
        }

        return $nodes;
    }
}
