<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $mapel->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $mapel->exists ? route('admin.mata-pelajaran.update', $mapel) : route('admin.mata-pelajaran.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($mapel->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="kode" value="Kode (contoh: MTK)" />
                    <x-text-input id="kode" name="kode" type="text" class="mt-1 block w-full"
                                  :value="old('kode', $mapel->kode)" required />
                    <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nama" value="Nama mata pelajaran" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                  :value="old('nama', $mapel->nama)" required />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.mata-pelajaran.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>