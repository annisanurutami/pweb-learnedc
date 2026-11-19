<?php

use App\Http\Controllers\Admin\HariController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'active'])->group(function () {
    // Login tunggal: semua role masuk lewat sini, lalu diarahkan sesuai role.
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    // Profil (bawaan Breeze).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');

        // A1: tahun ajaran dan hari aktif belajar
        Route::resource('tahun-ajaran', TahunAjaranController::class)
            ->parameters(['tahun-ajaran' => 'tahunAjaran'])
            ->except(['show']);
        Route::patch('tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])
            ->name('tahun-ajaran.aktifkan');

        Route::get('hari', [HariController::class, 'index'])->name('hari.index');
        Route::put('hari', [HariController::class, 'update'])->name('hari.update');

        // A3: semester
        Route::resource('semester', SemesterController::class)->except(['show']);
        Route::patch('semester/{semester}/aktifkan', [SemesterController::class, 'aktifkan'])
            ->name('semester.aktifkan');
    });

    Route::middleware('role:kepala_sekolah')->prefix('kepala-sekolah')->name('kepsek.')->group(function () {
        Route::get('/', [DashboardController::class, 'kepsek'])->name('dashboard');
    });

    Route::middleware('role:wali_kelas')->prefix('wali-kelas')->name('walikelas.')->group(function () {
        Route::get('/', [DashboardController::class, 'walikelas'])->name('dashboard');
    });

    Route::middleware('role:guru|wali_kelas')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/', [DashboardController::class, 'guru'])->name('dashboard');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [DashboardController::class, 'siswa'])->name('dashboard');
    });

    Route::middleware('role:bendahara')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/', [DashboardController::class, 'bendahara'])->name('dashboard');
    });
});

require __DIR__.'/auth.php';