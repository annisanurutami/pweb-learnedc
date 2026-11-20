<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penugasan Guru</h2>
            <a href="{{ route('admin.penugasan.create', ['kelas_id' => $kelasId]) }}"
               class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                Tambah
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <form method="GET" class="flex items-center gap-3 text-sm">
                <label for="kelas_id" class="text-gray-600">Kelas</label>
                <select id="kelas_id" name="kelas_id" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($daftarKelas as $k)
                        <option value="{{ $k->id }}" @selected($kelasId === $k->id)>
                            {{ $k->nama_kelas }} ({{ $k->tahunAjaran?->nama }})
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Mata pelajaran</th>
                            <th class="px-4 py-3">Guru pengampu</th>
                            <th class="px-4 py-3">Jadwal</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium">
                                    {{ $item->kelas->nama_kelas }}
                                    <span class="text-xs text-gray-500">({{ $item->kelas->tahunAjaran?->nama }})</span>
                                </td>
                                <td class="px-4 py-3">{{ $item->mapel->nama }}</td>
                                <td class="px-4 py-3">{{ $item->guru->nama }}</td>
                                <td class="px-4 py-3">{{ $item->jadwal_pelajaran_count }} sesi</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('admin.penugasan.edit', $item) }}" class="text-indigo-700 hover:underline">Ganti guru</a>
                                        <form method="POST" action="{{ route('admin.penugasan.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus penugasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada penugasan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>