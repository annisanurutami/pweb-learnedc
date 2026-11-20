<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jadwal Pelajaran</h2>
            @if ($semester)
                <a href="{{ route('admin.jadwal.create') }}"
                   class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                    Tambah
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @if (! $semester)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Belum ada semester aktif. Aktifkan satu semester dulu di menu Semester.
                </div>
            @else
                <p class="text-sm text-gray-600">
                    Semester aktif:
                    <span class="font-semibold">{{ $semester->nama }} {{ $semester->tahunAjaran?->nama }}</span>
                </p>

                <form method="GET" class="flex items-center gap-3 text-sm">
                    <label for="kelas_id" class="text-gray-600">Kelas</label>
                    <select id="kelas_id" name="kelas_id" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Semua kelas</option>
                        @foreach ($daftarKelas as $k)
                            <option value="{{ $k->id }}" @selected($kelasId === $k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Jam</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Mata pelajaran</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($daftar as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $item->hari->nama_hari }}</td>
                                    <td class="px-4 py-3">{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</td>
                                    <td class="px-4 py-3">{{ $item->guruMapel->kelas->nama_kelas }}</td>
                                    <td class="px-4 py-3">{{ $item->guruMapel->mapel->nama }}</td>
                                    <td class="px-4 py-3">{{ $item->guruMapel->guru->nama }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ route('admin.jadwal.edit', $item) }}" class="text-indigo-700 hover:underline">Ubah</a>
                                            <form method="POST" action="{{ route('admin.jadwal.destroy', $item) }}"
                                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada jadwal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>