<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nilai</h2>
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
                        <span>Rata-rata nilai</span>
                        <strong>{{ \App\Support\Angka::nilai($rataRata) }}</strong>
                        <small>Dari nilai akhir semua mata pelajaran</small>
                    </article>
                    <article class="stat-card">
                        <span>Mata pelajaran</span>
                        <strong>{{ $ringkasan->count() }}</strong>
                        <small>Di kelas {{ $konteks->kelas->nama_kelas }}</small>
                    </article>
                </section>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Mata pelajaran</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Tugas dinilai</th>
                                <th class="px-4 py-3">Rata-rata tugas</th>
                                <th class="px-4 py-3">Nilai ujian</th>
                                <th class="px-4 py-3">Nilai akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($ringkasan as $r)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $r['mapel'] }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $r['guru'] }}</td>
                                    <td class="px-4 py-3">{{ $r['tugas_dinilai'] }} / {{ $r['total_tugas'] }}</td>
                                    <td class="px-4 py-3">{{ \App\Support\Angka::nilai($r['tugas']) }}</td>
                                    <td class="px-4 py-3">{{ \App\Support\Angka::nilai($r['ujian']) }}</td>
                                    <td class="px-4 py-3 font-bold">{{ \App\Support\Angka::nilai($r['akhir']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada mata pelajaran untuk
                                        kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500">
                    Nilai akhir = tugas {{ $bobot['tugas'] }}% + ujian {{ $bobot['ujian'] }}%.
                    Komponen yang belum memiliki nilai tidak ikut dihitung.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>