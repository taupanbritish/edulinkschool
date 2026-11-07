<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WakasekKurikulumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function wakasek(): User
    {
        return User::where('email', 'wakasek@sekolah.id')->firstOrFail();
    }

    public function test_seeded_wakasek_account_uses_dedicated_role(): void
    {
        $wakasek = $this->wakasek();

        $this->assertSame(User::ROLE_WAKASEK_KURIKULUM, $wakasek->role);
        $this->assertSame('Wakasek Kurikulum', $wakasek->roleLabel());
        $this->assertTrue($wakasek->isPanelUser());
        $this->assertTrue($wakasek->isWakasekKurikulum());
        $this->assertFalse($wakasek->isReadOnly());
        $this->assertFalse($wakasek->canManage());
    }

    public function test_wakasek_dashboard_only_links_to_academic_menu(): void
    {
        $this->actingAs($this->wakasek());

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel Wakasek Kurikulum')
            ->assertSee(route('admin.teachers.index'))
            ->assertSee(route('admin.classrooms.index'))
            ->assertSee(route('admin.subjects.index'))
            ->assertSee(route('admin.schedules.index'))
            ->assertDontSee(route('admin.students.index'))
            ->assertDontSee(route('admin.announcements.index'))
            ->assertDontSee(route('admin.users.index'));
    }

    public function test_wakasek_can_manage_teaching_schedules(): void
    {
        $this->actingAs($this->wakasek());

        $assignment = TeachingAssignment::firstOrFail();

        $this->get(route('admin.schedules.index'))->assertOk();
        $this->get(route('admin.schedules.create'))->assertOk();

        $this->post(route('admin.schedules.store'), [
            'teaching_assignment_id' => $assignment->id,
            'day' => 6,
            'starts_at' => '20:05',
            'ends_at' => '21:00',
            'room' => 'Ruang Kurikulum',
        ])->assertRedirect(route('admin.schedules.index'));

        $schedule = Schedule::where('room', 'Ruang Kurikulum')->firstOrFail();

        $this->get(route('admin.schedules.edit', $schedule))->assertOk();

        $this->put(route('admin.schedules.update', $schedule), [
            'teaching_assignment_id' => $assignment->id,
            'day' => 6,
            'starts_at' => '20:15',
            'ends_at' => '21:15',
            'room' => 'Lab Kurikulum',
        ])->assertRedirect(route('admin.schedules.index'));

        $this->assertSame('Lab Kurikulum', $schedule->fresh()->room);

        $this->delete(route('admin.schedules.destroy', $schedule))->assertRedirect();

        $this->assertModelMissing($schedule);
    }

    public function test_wakasek_can_only_read_teachers_classrooms_and_subjects(): void
    {
        $this->actingAs($this->wakasek());

        $this->get(route('admin.teachers.index'))->assertOk();
        $this->get(route('admin.classrooms.index'))->assertOk();
        $this->get(route('admin.subjects.index'))->assertOk();

        $this->get(route('admin.teachers.create'))->assertForbidden();
        $this->get(route('admin.teachers.edit', Teacher::firstOrFail()))->assertForbidden();
        $this->get(route('admin.classrooms.create'))->assertForbidden();
        $this->get(route('admin.classrooms.edit', Classroom::firstOrFail()))->assertForbidden();
        $this->get(route('admin.subjects.create'))->assertForbidden();
        $this->get(route('admin.subjects.edit', Subject::firstOrFail()))->assertForbidden();

        $this->post(route('admin.teachers.store'), [])->assertForbidden();
        $this->post(route('admin.classrooms.store'), [])->assertForbidden();
        $this->post(route('admin.subjects.store'), [])->assertForbidden();
    }

    public function test_wakasek_cannot_open_other_admin_modules(): void
    {
        $this->actingAs($this->wakasek());

        foreach ([
            route('admin.students.index'),
            route('admin.guardians.index'),
            route('admin.journals.index'),
            route('admin.staff.index'),
            route('admin.organization.index'),
            route('admin.announcements.index'),
            route('admin.writings.index'),
            route('admin.contact-messages.index'),
            route('admin.facilities.index'),
            route('admin.extracurriculars.index'),
            route('admin.student-organizations.index'),
            route('admin.counseling-services.index'),
            route('admin.achievements.index'),
            route('admin.curriculum-groups.index'),
            route('admin.academic-events.index'),
            route('admin.alumni.index'),
            route('admin.users.index'),
            route('admin.settings.edit'),
            route('admin.backups.index'),
            route('admin.restore.form'),
            route('admin.imports.form', 'guru'),
        ] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->post(route('admin.accounts.generate', 'guru'))->assertForbidden();
    }
}
