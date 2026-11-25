<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kehadiran</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @if (!$konteks->siap())
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    @if (!$konteks->semester)
                        Belum ada semester aktif. Hubungi administrator.
                    @else
                        Anda belum terdaftar di kelas pada tahun ajaran ini. Hubungi administrator.
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-600">
                    Kelas <span class="font-semibold">{{ $konteks->kelas->nama_kelas }}</span>
                    &middot; Semester <span class="font-semibold">{{ $konteks->semester->nama }}</span>
                </p>

                <section class="stat-grid">
                    <article class="stat-card">
                        <span>Persentase hadir</span>
                        <strong>{{ $rekap['persen'] !== null ? \App\Support\Angka::nilai($rekap['persen'], 1) . '%' : '-' }}</strong>
                        <small>{{ $rekap['total'] }} pertemuan tercatat</small>
                    </article>
                    @foreach ($daftarStatus as $kode => $label)
                        <article class="stat-card">
                            <span>{{ $label }}</span>
                            <strong>{{ $rekap['jumlah'][$kode] }}</strong>
                            <small>pertemuan</small>
                        </article>
                    @endforeach
                </section>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <h3 class="px-4 pt-4 text-sm font-semibold text-gray-900">Riwayat kehadiran</h3>
                    <table class="mt-2 min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Mata pelajaran</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($rekap['baris'] as $p)
                                @php
                                    $kelasStatus = match ($p->status) {
                                        'H' => 'bg-green-50 text-green-700',
                                        'S' => 'bg-yellow-50 text-yellow-800',
                                        'I' => 'bg-blue-50 text-blue-700',
                                        default => 'bg-red-50 text-red-700',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">{{ $p->tanggal->format('d-m-Y') }}</td>
                                    <td class="px-4 py-3">
                                        {{ $p->jadwal_id ? ($rekap['jadwal']->get($p->jadwal_id)?->guruMapel->mapel->nama ?? '-') : 'Absensi harian kelas' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs {{ $kelasStatus }}">{{ $daftarStatus[$p->status] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $p->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada data kehadiran pada
                                        semester ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>