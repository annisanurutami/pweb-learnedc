<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelas Saya</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @if (! $semester)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Belum ada semester aktif. Hubungi administrator.
                </div>
            @else
                <p class="text-sm text-gray-600">
                    Semester aktif:
                    <span class="font-semibold">{{ $semester->nama }} {{ $semester->tahunAjaran?->nama }}</span>
                </p>

                @if ($daftar->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                        Anda belum ditugaskan mengajar di kelas mana pun pada tahun ajaran ini.
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($daftar as $item)
                            <div class="bg-white shadow-sm sm:rounded-lg p-5 space-y-3">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $item->mapel->nama }}</p>
                                    <p class="text-sm text-gray-600">Kelas {{ $item->kelas->nama_kelas }}</p>
                                </div>

                                <ul class="text-sm text-gray-600 space-y-1">
                                    @forelse ($item->jadwalPelajaran as $j)
                                        <li>{{ $j->hari->nama_hari }}, {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</li>
                                    @empty
                                        <li class="text-gray-400">Jadwal belum diatur.</li>
                                    @endforelse
                                </ul>

                                <a href="{{ route('guru.kelas.show', $item) }}"
                                   class="inline-block px-3 py-1.5 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                                    Buka kelas
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>