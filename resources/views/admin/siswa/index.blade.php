<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Akun Siswa</h2>
            <a href="{{ route('admin.siswa.create') }}"
               class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                Tambah
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @unless ($tahunAktif)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Belum ada tahun ajaran aktif, jadi kelas siswa belum bisa ditampilkan atau diatur.
                </div>
            @endunless

            <form method="GET" action="{{ route('admin.siswa.index') }}" class="flex items-center gap-2 text-sm">
                <label for="kelas" class="text-gray-600">Kelas:</label>
                <select id="kelas" name="kelas" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($daftarKelas as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) request('kelas') === (string) $kelas->id)>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                <button class="px-3 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md">Tampilkan</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftar as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                                <td class="px-4 py-3">{{ $item->nis }}</td>
                                <td class="px-4 py-3">{{ $item->user->email }}</td>
                                <td class="px-4 py-3">{{ $item->jenis_kelamin }}</td>
                                <td class="px-4 py-3">{{ $item->kelasSiswa->first()?->kelas?->nama_kelas ?? 'Belum ada kelas' }}</td>
                                <td class="px-4 py-3">
                                    @if ($item->user->is_active)
                                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('admin.siswa.edit', $item) }}" class="text-indigo-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.siswa.status', $item) }}"
                                              onsubmit="return confirm('Ubah status akun ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="{{ $item->user->is_active ? 'text-red-700' : 'text-green-700' }} hover:underline">
                                                {{ $item->user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada siswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $daftar->links() }}</div>
        </div>
    </div>
</x-app-layout>