<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tugas</h2>
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
            @else
                <p class="text-sm text-gray-600">
                    Kelas <span class="font-semibold">{{ $konteks->kelas->nama_kelas }}</span>
                    &middot; Semester <span class="font-semibold">{{ $konteks->semester->nama }}</span>
                </p>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Mata pelajaran</th>
                                <th class="px-4 py-3">Tugas</th>
                                <th class="px-4 py-3">Deadline (WIB)</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($daftar as $t)
                                <tr>
                                    <td class="px-4 py-3">{{ $t->guruMapel->mapel->nama }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $t->judul }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $t->deadline->format('d-m-Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $status[$t->id]['kelas'] }}">{{ $status[$t->id]['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('siswa.tugas.show', $t) }}" class="text-indigo-700 hover:underline">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada tugas pada semester ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>