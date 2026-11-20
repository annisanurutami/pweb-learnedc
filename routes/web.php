<?php

use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\GuruMapelController;
use App\Http\Controllers\Admin\HariController;
use App\Http\Controllers\Admin\JadwalPelajaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\KelasSayaController;
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

        // A2.2: mata pelajaran
        Route::resource('mata-pelajaran', MataPelajaranController::class)
            ->parameters(['mata-pelajaran' => 'mataPelajaran'])
            ->except(['show']);

        // A2.1: kelas (parameter ditulis eksplisit karena Laravel menyingkat 'kelas' menjadi 'kela')
        Route::resource('kelas', KelasController::class)
            ->parameters(['kelas' => 'kelas'])
            ->except(['show']);

        // A4: akun guru dan siswa (tidak dihapus, hanya dinonaktifkan)
        Route::resource('guru', GuruController::class)
            ->parameters(['guru' => 'guru'])
            ->except(['show', 'destroy']);
        Route::patch('guru/{guru}/status', [GuruController::class, 'ubahStatus'])->name('guru.status');

        Route::resource('siswa', SiswaController::class)
            ->parameters(['siswa' => 'siswa'])
            ->except(['show', 'destroy']);
        Route::patch('siswa/{siswa}/status', [SiswaController::class, 'ubahStatus'])->name('siswa.status');

        // A2.3 bagian 1: penugasan guru ke mapel dan kelas
        Route::resource('penugasan', GuruMapelController::class)
            ->parameters(['penugasan' => 'penugasan'])
            ->except(['show']);

        // A2.3 bagian 2: jadwal pelajaran (hari dan jam per penugasan)
        Route::resource('jadwal', JadwalPelajaranController::class)
            ->parameters(['jadwal' => 'jadwal'])
            ->except(['show']);
    });

    Route::middleware('role:kepala_sekolah')->prefix('kepala-sekolah')->name('kepsek.')->group(function () {
        Route::get('/', [DashboardController::class, 'kepsek'])->name('dashboard');
    });

    Route::middleware('role:wali_kelas')->prefix('wali-kelas')->name('walikelas.')->group(function () {
        Route::get('/', [DashboardController::class, 'walikelas'])->name('dashboard');
    });

    Route::middleware('role:guru|wali_kelas')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/', [DashboardController::class, 'guru'])->name('dashboard');

        // C1: kelas dan mapel yang diampu. Fitur guru lain bernaung di bawah kelas/{penugasan}.
        Route::get('kelas', [KelasSayaController::class, 'index'])->name('kelas.index');
        Route::get('kelas/{penugasan}', [KelasSayaController::class, 'show'])->name('kelas.show');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [DashboardController::class, 'siswa'])->name('dashboard');
    });

    Route::middleware('role:bendahara')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/', [DashboardController::class, 'bendahara'])->name('dashboard');
    });
});

require __DIR__.'/auth.php';