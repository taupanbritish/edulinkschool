<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\Facility;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    public function test_public_facilities_page_shows_description_image_and_video(): void
    {
        $facility = Facility::where('name', 'Perpustakaan Sekolah')->firstOrFail();

        $this->get(route('facilities'))
            ->assertOk()
            ->assertSee('Fasilitas Sekolah')
            ->assertSee($facility->name)
            ->assertSee('12.000 judul buku')
            ->assertSee('/media/photos/demo/berita-literasi.jpg', false)
            ->assertSee('youtube-nocookie.com/embed/jNQXAC9IVRw', false);
    }

    public function test_public_extracurricular_page_shows_description_image_and_video(): void
    {
        $extracurricular = Extracurricular::where('name', 'Robotika')->firstOrFail();

        $this->get(route('extracurriculars'))
            ->assertOk()
            ->assertSee('Ekstrakurikuler')
            ->assertSee($extracurricular->name)
            ->assertSee('merakit, dan memprogram robot')
            ->assertSee('/media/photos/demo/berita-robotika.jpg', false)
            ->assertSee('youtube-nocookie.com/embed/ScMzIvxBSi4', false);
    }

    public function test_public_navigation_links_to_new_menus(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('facilities').'"', false)
            ->assertSee('href="'.route('extracurriculars').'"', false);
    }

    public function test_admin_manages_facility_image_and_video(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.facilities.create'))->assertOk()->assertSee('Tambah Fasilitas');

        $this->post(route('admin.facilities.store'), [
            'name' => 'Ruang Musik',
            'description' => 'Ruang kedap suara dengan peralatan band dan piano.',
            'sort_order' => 7,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('musik.jpg', 800, 600),
            'video' => UploadedFile::fake()->create('tur.mp4', 2048, 'video/mp4'),
        ])->assertRedirect(route('admin.facilities.index'));

        $facility = Facility::where('name', 'Ruang Musik')->firstOrFail();

        $this->assertNotNull($facility->photo_path);
        $this->assertNotNull($facility->video_path);
        Storage::disk('public')->assertExists($facility->photo_path);
        Storage::disk('public')->assertExists($facility->video_path);

        $this->get('/media/'.$facility->photo_path)->assertOk();
        $this->get('/media/'.$facility->video_path)->assertOk();
        $this->get(route('admin.facilities.index'))->assertOk()->assertSee('Ruang Musik');
        $this->get(route('admin.facilities.edit', $facility))->assertOk()->assertSee('Ruang Musik');

        $oldPhoto = $facility->photo_path;
        $oldVideo = $facility->video_path;

        $this->put(route('admin.facilities.update', $facility), [
            'name' => 'Ruang Musik & Studio',
            'description' => 'Ruang kedap suara dengan peralatan band, piano, dan studio rekaman mini.',
            'sort_order' => 8,
            'is_active' => 1,
            'remove_photo' => 1,
            'remove_video' => 1,
            'video_url' => 'https://youtu.be/ScMzIvxBSi4',
        ])->assertRedirect(route('admin.facilities.index'));

        $facility->refresh();
        $this->assertNull($facility->photo_path);
        $this->assertNull($facility->video_path);
        $this->assertSame('https://youtu.be/ScMzIvxBSi4', $facility->video_url);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertMissing($oldVideo);

        $this->get(route('facilities'))
            ->assertOk()
            ->assertSee('Ruang Musik &amp; Studio', false)
            ->assertSee('youtube-nocookie.com/embed/ScMzIvxBSi4', false);

        $this->delete(route('admin.facilities.destroy', $facility))
            ->assertRedirect(route('admin.facilities.index'));

        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
    }

    public function test_admin_manages_extracurricular_and_deletes_its_files(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.extracurriculars.create'))->assertOk()->assertSee('Tambah Ekstrakurikuler');

        $this->post(route('admin.extracurriculars.store'), [
            'name' => 'Catur',
            'description' => 'Klub catur untuk mengasah strategi dan konsentrasi siswa.',
            'sort_order' => 6,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->image('catur.jpg', 800, 600),
        ])->assertRedirect(route('admin.extracurriculars.index'));

        $extracurricular = Extracurricular::where('name', 'Catur')->firstOrFail();
        $photoPath = $extracurricular->photo_path;

        Storage::disk('public')->assertExists($photoPath);
        $this->get(route('extracurriculars'))->assertOk()->assertSee('Catur');

        $this->delete(route('admin.extracurriculars.destroy', $extracurricular))
            ->assertRedirect(route('admin.extracurriculars.index'));

        $this->assertDatabaseMissing('extracurriculars', ['id' => $extracurricular->id]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_non_youtube_video_url_is_rejected(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.facilities.store'), [
            'name' => 'Fasilitas Palsu',
            'video_url' => 'https://vimeo.com/123456789',
            'is_active' => 1,
        ])->assertSessionHasErrors('video_url');

        $this->assertDatabaseMissing('facilities', ['name' => 'Fasilitas Palsu']);
    }

    public function test_inactive_entries_are_hidden_from_public_pages(): void
    {
        Facility::create([
            'name' => 'Gudang Tertutup',
            'sort_order' => 99,
            'is_active' => false,
        ]);

        $this->get(route('facilities'))
            ->assertOk()
            ->assertDontSee('Gudang Tertutup');

        $this->actingAs($this->admin())
            ->get(route('admin.facilities.index'))
            ->assertOk()
            ->assertSee('Gudang Tertutup')
            ->assertSee('Tanpa keterangan');
    }
}
