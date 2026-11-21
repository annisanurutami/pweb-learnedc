<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::view('/', 'learnedc.pages.login')
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->name('home');

Route::view('/learnedc', 'learnedc.pages.login')
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->name('learnedc.login');
Route::view('/learnedc/setup-admin', 'learnedc.pages.setup-admin')
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->name('learnedc.setup');

Route::get('/learnedc/{role}/{page?}', function (string $role, ?string $page = null) {
    $rolePages = [
        'admin' => [
            'label' => 'Admin Utama',
            'pages' => [
                'home' => ['view' => 'home-admin', 'label' => 'Ringkasan', 'icon' => 'fa-house'],
                'guru' => ['view' => 'teachers', 'label' => 'Data Guru', 'icon' => 'fa-chalkboard-user'],
                'siswa' => ['view' => 'students', 'label' => 'Data Siswa', 'icon' => 'fa-user-graduate'],
                'kelas' => ['view' => 'classes', 'label' => 'Kelas', 'icon' => 'fa-people-roof'],
                'mapel' => ['view' => 'subjects', 'label' => 'Mata Pelajaran', 'icon' => 'fa-book-open'],
                'tingkat' => ['view' => 'grade-levels', 'label' => 'Tingkat Kelas', 'icon' => 'fa-layer-group'],
                'jadwal' => ['view' => 'schedule', 'label' => 'Jadwal KBM', 'icon' => 'fa-calendar-days'],
                'piket' => ['view' => 'duties', 'label' => 'Jadwal Piket', 'icon' => 'fa-clipboard-check'],
                'pengumuman' => ['view' => 'announcements', 'label' => 'Pengumuman', 'icon' => 'fa-bullhorn'],
                'jenis-pelanggaran' => ['view' => 'violation-types', 'label' => 'Jenis Pelanggaran', 'icon' => 'fa-list-check'],
                'pengaturan' => ['view' => 'profile', 'label' => 'Pengaturan', 'icon' => 'fa-gear'],
            ],
        ],
        'guru-mapel' => [
            'label' => 'Guru Mata Pelajaran',
            'pages' => [
                'home' => ['view' => 'home-guru-mapel', 'label' => 'Ringkasan', 'icon' => 'fa-house'],
                'jadwal' => ['view' => 'schedule', 'label' => 'Jadwal Mengajar', 'icon' => 'fa-calendar-check'],
                'presensi' => ['view' => 'attendance', 'label' => 'Presensi Siswa', 'icon' => 'fa-clipboard-user'],
                'nilai' => ['view' => 'scores', 'label' => 'Input Nilai', 'icon' => 'fa-square-poll-vertical'],
                'piket' => ['view' => 'duties', 'label' => 'Jadwal Piket Guru', 'icon' => 'fa-clipboard-check'],
                'pengaturan' => ['view' => 'profile', 'label' => 'Pengaturan', 'icon' => 'fa-gear'],
            ],
        ],
        'guru-piket' => [
            'label' => 'Guru Piket',
            'pages' => [
                'home' => ['view' => 'home-guru-piket', 'label' => 'Ringkasan', 'icon' => 'fa-house'],
                'piket' => ['view' => 'duties', 'label' => 'Jadwal Piket', 'icon' => 'fa-clipboard-check'],
                'pelanggaran' => ['view' => 'teacher-violations', 'label' => 'Catatan Ketertiban', 'icon' => 'fa-triangle-exclamation'],
                'pengaturan' => ['view' => 'profile', 'label' => 'Pengaturan', 'icon' => 'fa-gear'],
            ],
        ],
        'wali-kelas' => [
            'label' => 'Wali Kelas',
            'pages' => [
                'home' => ['view' => 'home-wali-kelas', 'label' => 'Ringkasan', 'icon' => 'fa-house'],
                'kelas' => ['view' => 'classes', 'label' => 'Data Kelas', 'icon' => 'fa-people-roof'],
                'presensi' => ['view' => 'attendance', 'label' => 'Presensi Siswa', 'icon' => 'fa-clipboard-user'],
                'nilai' => ['view' => 'scores', 'label' => 'Input Nilai', 'icon' => 'fa-square-poll-vertical'],
                'piket' => ['view' => 'duties', 'label' => 'Piket Kelas', 'icon' => 'fa-clipboard-check'],
                'prestasi' => ['view' => 'achievements', 'label' => 'Prestasi Siswa', 'icon' => 'fa-trophy'],
                'pengaturan' => ['view' => 'profile', 'label' => 'Pengaturan', 'icon' => 'fa-gear'],
            ],
        ],
        'siswa' => [
            'label' => 'Siswa',
            'pages' => [
                'home' => ['view' => 'home-siswa', 'label' => 'Dashboard Saya', 'icon' => 'fa-house'],
                'jadwal-saya' => ['view' => 'student-schedule', 'label' => 'Jadwal Pelajaran', 'icon' => 'fa-calendar-days'],
                'nilai-saya' => ['view' => 'student-scores', 'label' => 'Nilai Saya', 'icon' => 'fa-square-poll-vertical'],
                'presensi-saya' => ['view' => 'student-attendance', 'label' => 'Presensi Saya', 'icon' => 'fa-clipboard-user'],
                'pengumuman-saya' => ['view' => 'student-announcements', 'label' => 'Pengumuman', 'icon' => 'fa-bullhorn'],
                'piket-saya' => ['view' => 'student-duty', 'label' => 'Piket Kelas', 'icon' => 'fa-clipboard-check'],
                'prestasi-saya' => ['view' => 'student-achievements', 'label' => 'Prestasi Saya', 'icon' => 'fa-trophy'],
                'pelanggaran-saya' => ['view' => 'student-violations', 'label' => 'Catatan BK', 'icon' => 'fa-triangle-exclamation'],
                'pengaturan' => ['view' => 'profile', 'label' => 'Pengaturan', 'icon' => 'fa-gear'],
            ],
        ],
    ];

    abort_unless(isset($rolePages[$role]), 404);
    $page ??= 'home';
    abort_unless(isset($rolePages[$role]['pages'][$page]), 404);

    $pageConfig = $rolePages[$role]['pages'][$page];

    return view('learnedc.pages.'.$pageConfig['view'], [
        'roleKey' => $role,
        'roleLabel' => $rolePages[$role]['label'],
        'pageKey' => $page,
        'pageTitle' => $pageConfig['label'],
        'pageIcon' => $pageConfig['icon'],
        'pageStyle' => $pageConfig['view'],
        'menuItems' => $rolePages[$role]['pages'],
    ]);
})->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])->name('learnedc.page');
