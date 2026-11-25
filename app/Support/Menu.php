<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Menu sidebar Learn's EDC, disusun dari role akun yang sedang login.
 *
 * Menambah menu saat modul baru selesai cukup menambah satu baris di ITEM.
 * Item dengan route yang belum ada otomatis dilewati.
 */
class Menu
{
    /** Urutan prioritas role, sama dengan User::dashboardRouteName(). */
    private const ROLE = [
        'super_admin' => 'Super Admin',
        'kepala_sekolah' => 'Kepala Sekolah',
        'wali_kelas' => 'Wali Kelas',
        'guru' => 'Guru',
        'bendahara' => 'Bendahara',
        'siswa' => 'Siswa',
    ];

    /**
     * Item menu per role, dikelompokkan menurut judul seksi.
     * Bentuk item: [label, ikon Font Awesome, nama route, pola route untuk status aktif].
     *
     * @var array<string, array<string, list<array{0: string, 1: string, 2: string, 3: list<string>}>>>
     */
    private const ITEM = [
        'super_admin' => [
            'DATA MASTER' => [
                ['Tahun Ajaran', 'fa-calendar-days', 'admin.tahun-ajaran.index', ['admin.tahun-ajaran.*']],
                ['Semester', 'fa-layer-group', 'admin.semester.index', ['admin.semester.*']],
                ['Hari Aktif', 'fa-calendar-check', 'admin.hari.index', ['admin.hari.*']],
                ['Kelas', 'fa-people-roof', 'admin.kelas.index', ['admin.kelas.*']],
                ['Mata Pelajaran', 'fa-book-open', 'admin.mata-pelajaran.index', ['admin.mata-pelajaran.*']],
            ],
            'AKADEMIK' => [
                ['Penugasan Guru', 'fa-chalkboard-user', 'admin.penugasan.index', ['admin.penugasan.*']],
                ['Jadwal Pelajaran', 'fa-clock', 'admin.jadwal.index', ['admin.jadwal.*']],
            ],
            'AKUN' => [
                ['Akun Guru', 'fa-user-tie', 'admin.guru.index', ['admin.guru.*']],
                ['Akun Siswa', 'fa-user-graduate', 'admin.siswa.index', ['admin.siswa.*']],
            ],
        ],
        'kepala_sekolah' => [],
        'wali_kelas' => [
            'MENU UTAMA' => [
                ['Kelas Saya', 'fa-school', 'guru.kelas.index', ['guru.kelas.*', 'guru.materi.*', 'guru.tugas.*', 'guru.nilai.*', 'guru.absensi.*']],
            ],
        ],
        'guru' => [
            'MENU UTAMA' => [
                ['Kelas Saya', 'fa-school', 'guru.kelas.index', ['guru.kelas.*', 'guru.materi.*', 'guru.tugas.*', 'guru.nilai.*', 'guru.absensi.*']],
            ],
        ],
        'bendahara' => [],
        'siswa' => [
            'MENU UTAMA' => [
                ['Materi', 'fa-book-open', 'siswa.materi.index', ['siswa.materi.*']],
                ['Tugas', 'fa-list-check', 'siswa.tugas.index', ['siswa.tugas.*']],
                ['Kehadiran', 'fa-clipboard-user', 'siswa.kehadiran.index', ['siswa.kehadiran.*']],
                ['Nilai', 'fa-chart-line', 'siswa.nilai.index', ['siswa.nilai.*']],
            ],
        ],
    ];

    /**
     * @return array{
     *     peran: string,
     *     ikon: string,
     *     seksi: list<array{judul: string, item: list<array{label: string, ikon: string, url: string, aktif: bool}>}>,
     *     pengaturanAktif: bool
     * }
     */
    public static function untuk(User $user): array
    {
        $dashboard = $user->dashboardRouteName();

        // Seksi pertama selalu "MENU UTAMA" dan berisi Beranda sesuai role utama.
        $seksi = [
            'MENU UTAMA' => [
                self::item('Beranda', 'fa-house', $dashboard, [$dashboard]),
            ],
        ];

        $peran = 'Pengguna';

        foreach (self::ROLE as $kode => $label) {
            if (!$user->hasRole($kode)) {
                continue;
            }

            if ($peran === 'Pengguna') {
                $peran = $label;
            }

            foreach (self::ITEM[$kode] as $judul => $daftar) {
                foreach ($daftar as [$teks, $ikon, $rute, $pola]) {
                    $seksi[$judul] ??= [];

                    // Role rangkap (misalnya guru dan wali kelas) tidak boleh menggandakan item.
                    $sudahAda = collect($seksi)->flatten(1)->contains(fn($i) => $i !== null && $i['rute'] === $rute);

                    if (!$sudahAda) {
                        $seksi[$judul][] = self::item($teks, $ikon, $rute, $pola);
                    }
                }
            }
        }

        $ikonAktif = 'fa-house';
        $hasil = [];

        foreach ($seksi as $judul => $daftar) {
            $daftar = array_values(array_filter($daftar));

            foreach ($daftar as $i) {
                if ($i['aktif']) {
                    $ikonAktif = $i['ikon'];
                }
            }

            if ($daftar !== []) {
                $hasil[] = ['judul' => $judul, 'item' => $daftar];
            }
        }

        $pengaturanAktif = request()->routeIs('profile.*');

        return [
            'peran' => $peran,
            'ikon' => $pengaturanAktif ? 'fa-gear' : $ikonAktif,
            'seksi' => $hasil,
            'pengaturanAktif' => $pengaturanAktif,
        ];
    }

    /**
     * @param  list<string>  $pola
     * @return array{label: string, ikon: string, rute: string, url: string, aktif: bool}|null
     */
    private static function item(string $label, string $ikon, string $rute, array $pola): ?array
    {
        if (!Route::has($rute)) {
            return null;
        }

        return [
            'label' => $label,
            'ikon' => $ikon,
            'rute' => $rute,
            'url' => route($rute),
            'aktif' => request()->routeIs(...$pola),
        ];
    }
}
