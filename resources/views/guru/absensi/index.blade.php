<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Absensi - {{ $penugasan->mapel->nama }} (Kelas {{ $penugasan->kelas->nama_kelas }})
            </h2>
            <a href="{{ route('guru.kelas.show', $penugasan) }}" class="text-sm text-gray-600 hover:underline">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.partials.pesan')

            @if (! $semester)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Belum ada semester aktif. Hubungi administrator.
                </div>
            @elseif ($daftarJadwal->isEmpty())
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Kelas ini belum punya jadwal pada semester aktif. Hubungi administrator.
                </div>
            @else
                {{-- Pilih pertemuan --}}
                <form method="GET" action="{{ route('guru.absensi.index', $penugasan) }}"
                      class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="jadwal_id" value="Jadwal" />
                        <select id="jadwal_id" name="jadwal_id" required
                                class="mt-1 block border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Pilih jadwal</option>
                            @foreach ($daftarJadwal as $j)
                                <option value="{{ $j->id }}" @selected((int) request('jadwal_id') === $j->id)>
                                    {{ $j->hari->nama_hari }}, {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="tanggal" value="Tanggal pertemuan" />
                        <input id="tanggal" name="tanggal" type="date" required
                               min="{{ $semester->tgl_mulai->format('Y-m-d') }}"
                               max="{{ now()->format('Y-m-d') }}"
                               value="{{ request('tanggal') }}"
                               class="mt-1 block border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <x-primary-button>Tampilkan</x-primary-button>
                </form>

                @if ($galat)
                    <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">{{ $galat }}</div>
                @endif

                {{-- Form kehadiran --}}
                @if ($siap)
                    @if ($daftarSiswa->isEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                            Belum ada siswa di kelas ini.
                        </div>
                    @else
                        <form method="POST" action="{{ route('guru.absensi.update', $penugasan) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                            <p class="mb-2 text-sm text-gray-700">
                                Pertemuan:
                                <span class="font-semibold">
                                    {{ $jadwal->hari->nama_hari }}, {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }},
                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                </span>
                            </p>

                            <x-input-error :messages="$errors->get('status')" class="mb-2" />
                            <x-input-error :messages="$errors->get('jadwal_id')" class="mb-2" />
                            <x-input-error :messages="$errors->get('tanggal')" class="mb-2" />

                            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="px-4 py-3">No</th>
                                            <th class="px-4 py-3">Siswa</th>
                                            <th class="px-4 py-3">Kehadiran</th>
                                            <th class="px-4 py-3">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @foreach ($daftarSiswa as $s)
                                            @php($terpilih = old('status.'.$s->id, $presensi->get($s->id)?->status ?? 'H'))
                                            <tr>
                                                <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-3">
                                                    <p class="font-medium">{{ $s->nama }}</p>
                                                    <p class="text-xs text-gray-500">NIS {{ $s->nis }}</p>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @foreach ($daftarStatus as $kode => $label)
                                                        <label class="inline-flex items-center gap-1 mr-3">
                                                            <input type="radio" name="status[{{ $s->id }}]" value="{{ $kode }}"
                                                                   @checked($terpilih === $kode)>
                                                            {{ $label }}
                                                        </label>
                                                    @endforeach
                                                    <x-input-error :messages="$errors->get('status.'.$s->id)" class="mt-1" />
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="text" name="keterangan[{{ $s->id }}]" maxlength="255"
                                                           value="{{ old('keterangan.'.$s->id, $presensi->get($s->id)?->keterangan) }}"
                                                           class="w-56 border-gray-300 rounded-md shadow-sm text-sm">
                                                    <x-input-error :messages="$errors->get('keterangan.'.$s->id)" class="mt-1" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <x-primary-button>Simpan absensi</x-primary-button>
                                <span class="text-xs text-gray-500">Keterangan hanya disimpan untuk Izin, Sakit, dan Alpa.</span>
                            </div>
                        </form>
                    @endif
                @endif

                {{-- Rekap --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <h3 class="px-4 pt-4 text-sm font-semibold text-gray-900">Rekap pertemuan terakhir</h3>
                    <table class="mt-2 min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Jadwal</th>
                                @foreach ($daftarStatus as $label)
                                    <th class="px-4 py-3">{{ $label }}</th>
                                @endforeach
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($rekap as $r)
                                <tr>
                                    <td class="px-4 py-3">{{ $r['tanggal']->format('d-m-Y') }}</td>
                                    <td class="px-4 py-3">
                                        {{ $r['jadwal']->hari->nama_hari }},
                                        {{ substr($r['jadwal']->jam_mulai, 0, 5) }} - {{ substr($r['jadwal']->jam_selesai, 0, 5) }}
                                    </td>
                                    @foreach ($daftarStatus as $kode => $label)
                                        <td class="px-4 py-3">{{ $r['jumlah'][$kode] }}</td>
                                    @endforeach
                                    <td class="px-4 py-3">
                                        <a href="{{ route('guru.absensi.index', ['penugasan' => $penugasan, 'jadwal_id' => $r['jadwal']->id, 'tanggal' => $r['tanggal']->format('Y-m-d')]) }}"
                                           class="text-indigo-700 hover:underline">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + count($daftarStatus) }}" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada pertemuan yang diabsen.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
