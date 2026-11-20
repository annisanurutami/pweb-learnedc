<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nilai Tugas - {{ $tugas->judul }}
            </h2>
            <a href="{{ route('guru.tugas.index', $penugasan) }}" class="text-sm text-gray-600 hover:underline">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-700 space-y-1">
                <p>Kelas: <span class="font-semibold">{{ $penugasan->kelas->nama_kelas }}</span>
                    - {{ $penugasan->mapel->nama }}</p>
                <p>Deadline (WIB): <span class="font-semibold">{{ $tugas->deadline->format('d-m-Y H:i') }}</span></p>
                <p class="text-xs text-gray-500">
                    Kolom nilai yang dikosongkan tidak mengubah nilai yang sudah tersimpan.
                    Setiap perubahan nilai dicatat dalam audit.
                </p>
            </div>

            @if ($daftarSiswa->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                    Belum ada siswa di kelas ini.
                </div>
            @else
                <form method="POST"
                      action="{{ route('guru.nilai.update', ['penugasan' => $penugasan, 'tugas' => $tugas]) }}">
                    @csrf
                    @method('PUT')

                    <x-input-error :messages="$errors->get('nilai')" class="mb-2" />

                    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Siswa</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Jawaban</th>
                                    <th class="px-4 py-3">Nilai (0-100)</th>
                                    <th class="px-4 py-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($daftarSiswa as $s)
                                    @php($p = $pengumpulan->get($s->id))
                                    <tr>
                                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium">{{ $s->nama }}</p>
                                            <p class="text-xs text-gray-500">NIS {{ $s->nis }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if (! $p || ! $p->submitted_at)
                                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">Belum mengumpulkan</span>
                                            @elseif ($p->submitted_at->greaterThan($tugas->deadline))
                                                <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-800 text-xs">Terlambat</span>
                                                <p class="mt-1 text-xs text-gray-500">{{ $p->submitted_at->format('d-m-Y H:i') }}</p>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs">Tepat waktu</span>
                                                <p class="mt-1 text-xs text-gray-500">{{ $p->submitted_at->format('d-m-Y H:i') }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($p && $p->file_path)
                                                <a href="{{ route('guru.nilai.berkas', ['penugasan' => $penugasan, 'tugas' => $tugas, 'siswa' => $s]) }}"
                                                   class="text-indigo-700 hover:underline">Unduh berkas</a>
                                            @endif
                                            @if ($p && $p->jawaban_teks)
                                                <details class="mt-1">
                                                    <summary class="cursor-pointer text-indigo-700">Jawaban teks</summary>
                                                    <p class="mt-1 whitespace-pre-line text-gray-700">{{ $p->jawaban_teks }}</p>
                                                </details>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="nilai[{{ $s->id }}]" min="0" max="100" step="0.01"
                                                   value="{{ old('nilai.'.$s->id, $p?->nilai) }}"
                                                   class="w-24 border-gray-300 rounded-md shadow-sm text-sm">
                                            <x-input-error :messages="$errors->get('nilai.'.$s->id)" class="mt-1" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="catatan[{{ $s->id }}]" maxlength="1000"
                                                   value="{{ old('catatan.'.$s->id, $p?->catatan_guru) }}"
                                                   class="w-64 border-gray-300 rounded-md shadow-sm text-sm">
                                            <x-input-error :messages="$errors->get('catatan.'.$s->id)" class="mt-1" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <x-primary-button>Simpan nilai</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>