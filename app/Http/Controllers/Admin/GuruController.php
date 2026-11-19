<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuruController extends Controller
{
    public function index(): View
    {
        return view('admin.guru.index', [
            'daftar' => Guru::with('user')
                ->withCount(['guruMapel', 'kelasWali'])
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.guru.form', ['guru' => new Guru()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $user = new User();
            $user->name = $data['nama'];
            $user->email = $data['email'];
            $user->password = $data['password']; // di-hash otomatis oleh cast 'hashed'
            $user->email_verified_at = now();
            $user->is_active = true;
            $user->save();
            $user->assignRole('guru');

            Guru::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'] ?? null,
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Akun guru ditambahkan.');
    }

    public function edit(Guru $guru): View
    {
        return view('admin.guru.form', ['guru' => $guru->load('user')]);
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $data = $this->validasi($request, $guru);

        DB::transaction(function () use ($data, $guru) {
            $guru->update([
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'] ?? null,
            ]);

            $user = $guru->user;
            $user->name = $data['nama'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru diperbarui.');
    }

    /** Akun tidak dihapus karena nilai, absensi, dan jadwal menunjuk padanya. Cukup dinonaktifkan. */
    public function ubahStatus(Guru $guru): RedirectResponse
    {
        $user = $guru->user;
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'Akun guru diaktifkan.' : 'Akun guru dinonaktifkan.');
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, ?Guru $guru = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($guru?->user_id),
            ],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($guru?->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => [$guru ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
    }
}