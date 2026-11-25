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
use App\Http\Controllers\Guru\AbsensiSiswaController;
use App\Http\Controllers\Guru\KelasSayaController;
use App\Http\Controllers\Guru\MateriController;
use App\Http\Controllers\Guru\NilaiTugasController;
use App\Http\Controllers\Guru\TugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\KehadiranSiswaController;
use App\Http\Controllers\Siswa\MateriSiswaController;
use App\Http\Controllers\Siswa\NilaiSiswaController;
use App\Http\Controllers\Siswa\TugasSiswaController;
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

        // C2: materi per kelas dan mapel
        Route::get('kelas/{penugasan}/materi', [MateriController::class, 'index'])->name('materi.index');
        Route::get('kelas/{penugasan}/materi/create', [MateriController::class, 'create'])->name('materi.create');
        Route::post('kelas/{penugasan}/materi', [MateriController::class, 'store'])->name('materi.store');
        Route::get('kelas/{penugasan}/materi/{materi}/edit', [MateriController::class, 'edit'])->name('materi.edit');
        Route::put('kelas/{penugasan}/materi/{materi}', [MateriController::class, 'update'])->name('materi.update');
        Route::delete('kelas/{penugasan}/materi/{materi}', [MateriController::class, 'destroy'])->name('materi.destroy');
        Route::get('kelas/{penugasan}/materi/{materi}/unduh', [MateriController::class, 'unduh'])->name('materi.unduh');

        // C3: tugas per kelas dan mapel
        Route::get('kelas/{penugasan}/tugas', [TugasController::class, 'index'])->name('tugas.index');
        Route::get('kelas/{penugasan}/tugas/create', [TugasController::class, 'create'])->name('tugas.create');
        Route::post('kelas/{penugasan}/tugas', [TugasController::class, 'store'])->name('tugas.store');
        Route::get('kelas/{penugasan}/tugas/{tugas}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
        Route::put('kelas/{penugasan}/tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
        Route::delete('kelas/{penugasan}/tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
        Route::get('kelas/{penugasan}/tugas/{tugas}/lampiran', [TugasController::class, 'lampiran'])->name('tugas.lampiran');

        // C3.1 dan C3.2: nilai tugas
        Route::get('kelas/{penugasan}/tugas/{tugas}/nilai', [NilaiTugasController::class, 'index'])->name('nilai.index');
        Route::put('kelas/{penugasan}/tugas/{tugas}/nilai', [NilaiTugasController::class, 'update'])->name('nilai.update');
        Route::get('kelas/{penugasan}/tugas/{tugas}/nilai/{siswa}/berkas', [NilaiTugasController::class, 'berkas'])->name('nilai.berkas');

        // C5: absensi siswa per pertemuan
        Route::get('kelas/{penugasan}/absensi', [AbsensiSiswaController::class, 'index'])->name('absensi.index');
        Route::put('kelas/{penugasan}/absensi', [AbsensiSiswaController::class, 'update'])->name('absensi.update');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [DashboardController::class, 'siswa'])->name('dashboard');

        // E3 dan E5: tugas dan pengumpulan jawaban
        Route::get('tugas', [TugasSiswaController::class, 'index'])->name('tugas.index');
        Route::get('tugas/{tugas}', [TugasSiswaController::class, 'show'])->name('tugas.show');
        Route::put('tugas/{tugas}/jawaban', [TugasSiswaController::class, 'kumpul'])->name('tugas.kumpul');
        Route::get('tugas/{tugas}/lampiran', [TugasSiswaController::class, 'lampiran'])->name('tugas.lampiran');
        Route::get('tugas/{tugas}/jawaban/unduh', [TugasSiswaController::class, 'jawaban'])->name('tugas.jawaban');

        // Materi dari guru
        Route::get('materi', [MateriSiswaController::class, 'index'])->name('materi.index');
        Route::get('materi/{materi}/unduh', [MateriSiswaController::class, 'unduh'])->name('materi.unduh');

        // E1: kehadiran dan nilai
        Route::get('kehadiran', [KehadiranSiswaController::class, 'index'])->name('kehadiran.index');
        Route::get('nilai', [NilaiSiswaController::class, 'index'])->name('nilai.index');
    });

    Route::middleware('role:bendahara')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/', [DashboardController::class, 'bendahara'])->name('dashboard');
    });
});

require __DIR__.'/auth.php';