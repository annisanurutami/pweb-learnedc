<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $semester->exists ? 'Ubah Semester' : 'Tambah Semester' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $semester->exists ? route('admin.semester.update', $semester) : route('admin.semester.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($semester->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="tahun_ajaran_id" value="Tahun ajaran" />
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih tahun ajaran</option>
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}" @selected(old('tahun_ajaran_id', $semester->tahun_ajaran_id) == $tahun->id)>
                                {{ $tahun->nama }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tahun_ajaran_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nama" value="Semester" />
                    <select id="nama" name="nama" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @foreach (['Ganjil', 'Genap'] as $pilihan)
                            <option value="{{ $pilihan }}" @selected(old('nama', $semester->nama) === $pilihan)>{{ $pilihan }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tgl_mulai" value="Tanggal mulai" />
                    <x-text-input id="tgl_mulai" name="tgl_mulai" type="date" class="mt-1 block w-full"
                                  :value="old('tgl_mulai', $semester->tgl_mulai?->format('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('tgl_mulai')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tgl_selesai" value="Tanggal selesai" />
                    <x-text-input id="tgl_selesai" name="tgl_selesai" type="date" class="mt-1 block w-full"
                                  :value="old('tgl_selesai', $semester->tgl_selesai?->format('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('tgl_selesai')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.semester.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>