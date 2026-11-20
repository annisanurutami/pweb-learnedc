<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $penugasan->mapel->nama }} - Kelas {{ $penugasan->kelas->nama_kelas }}
            </h2>
            <a href="{{ route('guru.kelas.index') }}" class="text-sm text-gray-600 hover:underline">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3 text-sm text-gray-700">
                <p>Tahun ajaran:
                    <span class="font-semibold">{{ $penugasan->kelas->tahunAjaran?->nama }}</span>
                </p>
                <p>Semester aktif:
                    <span class="font-semibold">
                        {{ $semester ? $semester->nama.' '.$semester->tahunAjaran?->nama : 'Belum ada semester aktif' }}
                    </span>
                </p>
                <p>Wali kelas:
                    <span class="font-semibold">{{ $penugasan->kelas->waliKelas?->nama ?? '-' }}</span>
                </p>
                <p>Jumlah siswa:
                    <span class="font-semibold">{{ $jumlahSiswa }}</span>
                </p>

                <div>
                    <p>Jadwal:</p>
                    <ul class="mt-1 space-y-1">
                        @forelse ($jadwal as $j)
                            <li class="font-semibold">
                                {{ $j->hari->nama_hari }}, {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                            </li>
                        @empty
                            <li class="text-gray-400">Jadwal belum diatur.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900">Menu kelas</h3>
                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('guru.materi.index', $penugasan) }}"
                       class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                        Materi ({{ $jumlahMateri }})
                    </a>
                </div>
            </div>

            <p class="text-xs text-gray-400">
                Menu tugas, ujian, dan absensi untuk kelas ini ditambahkan pada langkah berikutnya.
            </p>
        </div>
    </div>
</x-app-layout>