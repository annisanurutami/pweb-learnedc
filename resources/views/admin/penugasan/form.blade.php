<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $penugasan->exists ? 'Ganti Guru Pengampu' : 'Tambah Penugasan Guru' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $penugasan->exists ? route('admin.penugasan.update', $penugasan) : route('admin.penugasan.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($penugasan->exists)
                    @method('PUT')
                @endif

                @if ($penugasan->exists)
                    <div class="text-sm text-gray-700 space-y-1">
                        <p>Kelas: <span class="font-semibold">{{ $penugasan->kelas->nama_kelas }}</span></p>
                        <p>Mata pelajaran: <span class="font-semibold">{{ $penugasan->mapel->nama }}</span></p>
                    </div>
                @else
                    <div>
                        <x-input-label for="kelas_id" value="Kelas" />
                        <select id="kelas_id" name="kelas_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Pilih kelas</option>
                            @foreach ($daftarKelas as $k)
                                <option value="{{ $k->id }}" @selected((int) old('kelas_id', $penugasan->kelas_id) === $k->id)>
                                    {{ $k->nama_kelas }} ({{ $k->tahunAjaran?->nama }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mapel_id" value="Mata pelajaran" />
                        <select id="mapel_id" name="mapel_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Pilih mata pelajaran</option>
                            @foreach ($daftarMapel as $m)
                                <option value="{{ $m->id }}" @selected((int) old('mapel_id') === $m->id)>
                                    {{ $m->kode }} - {{ $m->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mapel_id')" class="mt-2" />
                    </div>
                @endif

                <div>
                    <x-input-label for="guru_id" value="Guru pengampu" />
                    <select id="guru_id" name="guru_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih guru</option>
                        @foreach ($daftarGuru as $g)
                            <option value="{{ $g->id }}" @selected((int) old('guru_id', $penugasan->guru_id) === $g->id)>
                                {{ $g->nama }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('guru_id')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.penugasan.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>