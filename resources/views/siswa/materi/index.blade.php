<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Materi</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @if (! $konteks->siap())
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    @if (! $konteks->semester)
                        Belum ada semester aktif. Hubungi administrator.
                    @else
                        Anda belum terdaftar di kelas pada tahun ajaran ini. Hubungi administrator.
                    @endif
                </div>
            @elseif ($perMapel->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                    Belum ada materi pada semester ini.
                </div>
            @else
                <p class="text-sm text-gray-600">
                    Kelas <span class="font-semibold">{{ $konteks->kelas->nama_kelas }}</span>
                    &middot; Semester <span class="font-semibold">{{ $konteks->semester->nama }}</span>
                </p>

                @foreach ($perMapel as $namaMapel => $daftar)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-base font-semibold text-gray-900">{{ $namaMapel }}</h3>

                        <ul class="mt-3 divide-y">
                            @foreach ($daftar as $m)
                                <li class="py-3 flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900">{{ $m->judul }}</p>
                                        @if ($m->deskripsi)
                                            <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($m->deskripsi, 140) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">{{ $m->created_at?->format('d-m-Y') }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-3 text-sm">
                                        @if ($m->file_path)
                                            <a href="{{ route('siswa.materi.unduh', $m) }}" class="text-indigo-700 hover:underline">Unduh file</a>
                                        @endif
                                        @if ($m->link_url)
                                            <a href="{{ $m->link_url }}" target="_blank" rel="noopener noreferrer"
                                               class="text-indigo-700 hover:underline">Buka link</a>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>