<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** /dashboard => arahkan ke dashboard sesuai role. */
    public function redirect(): RedirectResponse
    {
        return redirect()->route(auth()->user()->dashboardRouteName());
    }

    public function admin(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Super Admin']);
    }

    public function kepsek(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Kepala Sekolah']);
    }

    public function walikelas(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Wali Kelas']);
    }

    public function guru(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Guru']);
    }

    public function siswa(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Siswa']);
    }

    public function bendahara(): View
    {
        return view('dashboard', ['judul' => 'Dashboard Bendahara']);
    }
}