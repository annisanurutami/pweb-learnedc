<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $siswa->exists ? 'Ubah Akun Siswa' : 'Tambah Akun Siswa' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <form method="POST"
                  action="{{ $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($siswa->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nama" value="Nama lengkap" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                  :value="old('nama', $siswa->nama)" required />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email (untuk login)" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  :value="old('email', $siswa->user?->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nis" value="NIS" />
                    <x-text-input id="nis" name="nis" type="text" class="mt-1 block w-full"
                                  :value="old('nis', $siswa->nis)" required />
                    <x-input-error :messages="$errors->get('nis')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nisn" value="NISN (boleh kosong)" />
                    <x-text-input id="nisn" name="nisn" type="text" class="mt-1 block w-full"
                                  :value="old('nisn', $siswa->nisn)" />
                    <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="jenis_kelamin" value="Jenis kelamin" />
                    <select id="jenis_kelamin" name="jenis_kelamin" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                    <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tgl_lahir" value="Tanggal lahir (boleh kosong)" />
                    <x-text-input id="tgl_lahir" name="tgl_lahir" type="date" class="mt-1 block w-full"
                                  :value="old('tgl_lahir', $siswa->tgl_lahir?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('tgl_lahir')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="kelas_id" :value="'Kelas (tahun ajaran ' . ($tahunAktif?->nama ?? 'belum aktif') . ')'" />
                    <select id="kelas_id" name="kelas_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Belum ditempatkan</option>
                        @foreach ($daftarKelas as $kelas)
                            <option value="{{ $kelas->id }}"
                                @selected((string) old('kelas_id', $kelasSekarang) === (string) $kelas->id)>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password"
                        :value="$siswa->exists ? 'Password baru (kosongkan jika tidak diganti)' : 'Password (minimal 8 karakter)'" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                  :required="! $siswa->exists" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.siswa.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>