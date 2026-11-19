<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $tahunAjaran->exists ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $tahunAjaran->exists ? route('admin.tahun-ajaran.update', $tahunAjaran) : route('admin.tahun-ajaran.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($tahunAjaran->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nama" value="Nama (contoh: 2026/2027)" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                  :value="old('nama', $tahunAjaran->nama)" required />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tgl_mulai" value="Tanggal mulai" />
                    <x-text-input id="tgl_mulai" name="tgl_mulai" type="date" class="mt-1 block w-full"
                                  :value="old('tgl_mulai', $tahunAjaran->tgl_mulai?->format('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('tgl_mulai')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tgl_selesai" value="Tanggal selesai" />
                    <x-text-input id="tgl_selesai" name="tgl_selesai" type="date" class="mt-1 block w-full"
                                  :value="old('tgl_selesai', $tahunAjaran->tgl_selesai?->format('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('tgl_selesai')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.tahun-ajaran.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>