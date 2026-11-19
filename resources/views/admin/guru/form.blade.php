<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $guru->exists ? 'Ubah Akun Guru' : 'Tambah Akun Guru' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $guru->exists ? route('admin.guru.update', $guru) : route('admin.guru.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($guru->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nama" value="Nama lengkap" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                  :value="old('nama', $guru->nama)" required />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email (untuk login)" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  :value="old('email', $guru->user?->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nip" value="NIP (boleh kosong)" />
                    <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full"
                                  :value="old('nip', $guru->nip)" />
                    <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="no_hp" value="No. HP (boleh kosong)" />
                    <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full"
                                  :value="old('no_hp', $guru->no_hp)" />
                    <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password"
                        :value="$guru->exists ? 'Password baru (kosongkan jika tidak diganti)' : 'Password (minimal 8 karakter)'" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                  :required="! $guru->exists" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.guru.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>