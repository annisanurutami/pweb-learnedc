<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelas</h2>
            <a href="{{ route('admin.kelas.create') }}"
               class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                Tambah
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Tingkat</th>
                            <th class="px-4 py-3">Tahun ajaran</th>
                            <th class="px-4 py-3">Wali kelas</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Mapel</th>
                            <th class="px-4 py-3">Iuran kas</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $item->nama_kelas }}</td>
                                <td class="px-4 py-3">{{ $item->tingkat ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->tahunAjaran->nama }}</td>
                                <td class="px-4 py-3">{{ $item->waliKelas?->nama ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->kelas_siswa_count }}</td>
                                <td class="px-4 py-3">{{ $item->guru_mapel_count }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($item->iuran_kas, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('admin.kelas.edit', $item) }}" class="text-indigo-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.kelas.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus kelas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>