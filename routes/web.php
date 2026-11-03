<?php

use App\Http\Controllers\Admin\AcademicEventController;
use App\Http\Controllers\Admin\AccountGenerationController;
use App\Http\Controllers\Admin\AchievementController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CounselingServiceController;
use App\Http\Controllers\Admin\CurriculumGroupController;
use App\Http\Controllers\Admin\DatabaseBackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExtracurricularController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\OrganizationMemberController;
use App\Http\Controllers\Admin\PanelUserController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentOrganizationController;
use App\Http\Controllers\Admin\StudentWritingController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guru\AttendanceController;
use App\Http\Controllers\Guru\GradeController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\Guru\ReportController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/profil', [PublicController::class, 'profile'])->name('profile');
Route::get('/profil/guru', [PublicController::class, 'teachers'])->name('teachers');
Route::get('/tata-usaha', [PublicController::class, 'staff'])->name('staff');
Route::get('/struktur-organisasi', [PublicController::class, 'organization'])->name('organization');
Route::get('/fasilitas', [PublicController::class, 'facilities'])->name('facilities');
Route::get('/ekstrakurikuler', [PublicController::class, 'extracurriculars'])->name('extracurriculars');
Route::get('/kesiswaan/organisasi-siswa', [PublicController::class, 'studentOrganizations'])->name('student-organizations');
Route::get('/kesiswaan/bimbingan-konseling', [PublicController::class, 'counseling'])->name('counseling');
Route::get('/prestasi/akademik', [PublicController::class, 'academicAchievements'])->name('achievements.academic');
Route::get('/prestasi/non-akademik', [PublicController::class, 'nonAcademicAchievements'])->name('achievements.non_academic');
Route::get('/akademik/kurikulum', [PublicController::class, 'curriculum'])->name('curriculum');
Route::get('/akademik/kalender', [PublicController::class, 'academicCalendar'])->name('academic-calendar');
Route::get('/akademik/alumni', [PublicController::class, 'alumni'])->name('alumni');
Route::get('/kontak', [PublicController::class, 'contact'])->name('contact');
Route::post('/kontak', [PublicController::class, 'sendContactMessage'])
    ->middleware('throttle:5,1')
    ->name('contact.send');
Route::get('/cari', [PublicController::class, 'search'])->name('search');
Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/majalah', [PublicController::class, 'writings'])->name('writings.index');
Route::get('/berita/majalah/{studentWriting:slug}', [PublicController::class, 'writing'])->name('writings.show');
Route::get('/berita/{announcement:slug}', [NewsController::class, 'show'])->name('news.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin,petugas,kepala_sekolah', 'panel.writable'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::post('accounts/generate/{type}', [AccountGenerationController::class, 'generate'])
        ->whereIn('type', ['guru', 'siswa', 'wali'])
        ->name('accounts.generate');

    Route::resource('students', StudentController::class)->except('show');
    Route::resource('teachers', TeacherController::class)->except('show');
    Route::resource('guardians', GuardianController::class)->except('show');
    Route::resource('classrooms', ClassroomController::class)->except('show');
    Route::resource('subjects', SubjectController::class)->except('show');
    Route::delete('subjects/{subject}/assignments/{assignment}', [SubjectController::class, 'destroyAssignment'])
        ->scopeBindings()
        ->name('subjects.assignments.destroy');
    Route::resource('schedules', ScheduleController::class)->except('show');
    Route::resource('staff', StaffController::class)->except('show');
    Route::resource('organization', OrganizationMemberController::class)->except('show');
    Route::resource('announcements', AnnouncementController::class)->except('show');
    Route::resource('writings', StudentWritingController::class)->except('show');
    Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);
    Route::resource('facilities', FacilityController::class)->except('show');
    Route::resource('extracurriculars', ExtracurricularController::class)->except('show');
    Route::resource('student-organizations', StudentOrganizationController::class)->except('show');
    Route::resource('counseling-services', CounselingServiceController::class)->except('show');
    Route::resource('achievements', AchievementController::class)->except('show');
    Route::resource('curriculum-groups', CurriculumGroupController::class)->except('show');
    Route::resource('academic-events', AcademicEventController::class)->except('show');
    Route::resource('alumni', AlumniController::class)->except('show')->parameters(['alumni' => 'alumni']);

    Route::get('import/{type}/template', [ImportController::class, 'template'])
        ->whereIn('type', ['siswa', 'guru', 'kelas', 'mapel'])
        ->name('imports.template');
    Route::get('import/{type}', [ImportController::class, 'form'])
        ->whereIn('type', ['siswa', 'guru', 'kelas', 'mapel'])
        ->name('imports.form');
    Route::post('import/{type}', [ImportController::class, 'import'])
        ->whereIn('type', ['siswa', 'guru', 'kelas', 'mapel'])
        ->name('imports.store');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', PanelUserController::class)->except('show');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('backups', [DatabaseBackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [DatabaseBackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{file}/download', [DatabaseBackupController::class, 'download'])
            ->where('file', '[A-Za-z0-9][A-Za-z0-9._-]*')
            ->name('backups.download');
        Route::delete('backups/{file}', [DatabaseBackupController::class, 'destroy'])
            ->where('file', '[A-Za-z0-9][A-Za-z0-9._-]*')
            ->name('backups.destroy');

        Route::get('restore', [DatabaseBackupController::class, 'restoreForm'])->name('restore.form');
        Route::post('restore', [DatabaseBackupController::class, 'restore'])->name('restore.store');
    });
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', GuruDashboardController::class)->name('dashboard');
    Route::get('nilai', [GradeController::class, 'index'])->name('grades.index');
    Route::get('nilai/export/{format}', [ReportController::class, 'grades'])
        ->whereIn('format', ['pdf', 'xlsx'])
        ->name('grades.export');
    Route::post('nilai', [GradeController::class, 'store'])->name('grades.store');
    Route::delete('nilai/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
    Route::get('absensi', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('absensi/export/{format}', [ReportController::class, 'attendance'])
        ->whereIn('format', ['pdf', 'xlsx'])
        ->name('attendance.export');
    Route::post('absensi', [AttendanceController::class, 'store'])->name('attendance.store');
});

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin', 'petugas', 'kepala_sekolah' => redirect()->route('admin.dashboard'),
        'guru' => redirect()->route('guru.dashboard'),
        default => redirect()->route('home'),
    };
})->middleware('auth')->name('dashboard');
