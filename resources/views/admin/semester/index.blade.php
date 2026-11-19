<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Semester</h2>
            <a href="{{ route('admin.semester.create') }}"
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
                            <th class="px-4 py-3">Tahun ajaran</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->tahunAjaran->nama }}</td>
                                <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                                <td class="px-4 py-3">{{ $item->tgl_mulai->format('d M Y') }} - {{ $item->tgl_selesai->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($item->is_active)
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs">Tidak aktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        @unless ($item->is_active)
                                            <form method="POST" action="{{ route('admin.semester.aktifkan', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="text-green-700 hover:underline">Aktifkan</button>
                                            </form>
                                        @endunless
                                        <a href="{{ route('admin.semester.edit', $item) }}" class="text-indigo-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.semester.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus semester ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada semester.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>