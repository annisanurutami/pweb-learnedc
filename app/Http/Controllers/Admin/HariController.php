<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hari;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HariController extends Controller
{
    public function index(): View
    {
        return view('admin.hari.index', [
            'daftar' => Hari::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'aktif' => ['nullable', 'array'],
            'aktif.*' => ['integer', 'exists:hari,id'],
        ]);

        Hari::query()->update(['is_active' => false]);
        Hari::whereIn('id', $data['aktif'] ?? [])->update(['is_active' => true]);

        return back()->with('success', 'Hari aktif belajar diperbarui.');
    }
}