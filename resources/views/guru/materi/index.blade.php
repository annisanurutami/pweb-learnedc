<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Materi - {{ $penugasan->mapel->nama }} (Kelas {{ $penugasan->kelas->nama_kelas }})
            </h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('guru.kelas.show', $penugasan) }}" class="text-sm text-gray-600 hover:underline">Kembali</a>
                @if ($semester)
                    <a href="{{ route('guru.materi.create', $penugasan) }}"
                       class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                        Tambah
                    </a>
                @endif
            </div>
        </div>
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
                    <span class="font-semibold">{{ $semester->nama }}</span>
                </p>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Judul</th>
                                <th class="px-4 py-3">Berkas</th>
                                <th class="px-4 py-3">Diunggah</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($daftar as $m)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ $m->judul }}</p>
                                        @if ($m->deskripsi)
                                            <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($m->deskripsi, 90) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @if ($m->file_path)
                                                <a href="{{ route('guru.materi.unduh', ['penugasan' => $penugasan, 'materi' => $m]) }}"
                                                   class="text-indigo-700 hover:underline">Unduh file</a>
                                            @endif
                                            @if ($m->link_url)
                                                <a href="{{ $m->link_url }}" target="_blank" rel="noopener noreferrer"
                                                   class="text-indigo-700 hover:underline">Buka link</a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $m->created_at?->format('d-m-Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ route('guru.materi.edit', ['penugasan' => $penugasan, 'materi' => $m]) }}"
                                               class="text-indigo-700 hover:underline">Ubah</a>
                                            <form method="POST"
                                                  action="{{ route('guru.materi.destroy', ['penugasan' => $penugasan, 'materi' => $m]) }}"
                                                  onsubmit="return confirm('Hapus materi ini beserta filenya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada materi pada semester ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>