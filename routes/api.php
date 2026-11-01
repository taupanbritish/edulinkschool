<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('sekolah', [SchoolController::class, 'show']);
Route::get('pengumuman', [SchoolController::class, 'announcements']);
Route::get('pengumuman/{announcement:slug}', [SchoolController::class, 'announcement']);

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('role:siswa,orang_tua')->group(function () {
        Route::get('anak', [ProfileController::class, 'children']);
        Route::get('beranda', DashboardController::class);
        Route::get('jadwal', [ScheduleController::class, 'index']);
        Route::get('nilai', [GradeController::class, 'index']);
        Route::get('absensi', [AttendanceController::class, 'index']);
        Route::get('profil', [ProfileController::class, 'index']);
    });
});
