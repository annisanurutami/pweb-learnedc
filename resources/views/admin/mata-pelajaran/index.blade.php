<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mata Pelajaran</h2>
            <a href="{{ route('admin.mata-pelajaran.create') }}"
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
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Diampu di (kelas)</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $item->kode }}</td>
                                <td class="px-4 py-3">{{ $item->nama }}</td>
                                <td class="px-4 py-3">{{ $item->guru_mapel_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('admin.mata-pelajaran.edit', $item) }}" class="text-indigo-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.mata-pelajaran.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada mata pelajaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>