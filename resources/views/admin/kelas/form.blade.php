<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $kelas->exists ? 'Ubah Kelas' : 'Tambah Kelas' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($kelas->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="tahun_ajaran_id" value="Tahun ajaran" />
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih tahun ajaran</option>
                        @foreach ($daftarTahun as $tahun)
                            <option value="{{ $tahun->id }}"
                                @selected((string) old('tahun_ajaran_id', $kelas->tahun_ajaran_id ?? $tahunAktifId) === (string) $tahun->id)>
                                {{ $tahun->nama }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tahun_ajaran_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nama_kelas" value="Nama kelas (contoh: X-A)" />
                    <x-text-input id="nama_kelas" name="nama_kelas" type="text" class="mt-1 block w-full"
                                  :value="old('nama_kelas', $kelas->nama_kelas)" required />
                    <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tingkat" value="Tingkat (contoh: X)" />
                    <x-text-input id="tingkat" name="tingkat" type="text" class="mt-1 block w-full"
                                  :value="old('tingkat', $kelas->tingkat)" />
                    <x-input-error :messages="$errors->get('tingkat')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="wali_kelas_id" value="Wali kelas" />
                    <select id="wali_kelas_id" name="wali_kelas_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Belum ditentukan</option>
                        @foreach ($daftarGuru as $guru)
                            <option value="{{ $guru->id }}"
                                @selected((string) old('wali_kelas_id', $kelas->wali_kelas_id) === (string) $guru->id)>
                                {{ $guru->nama }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('wali_kelas_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="iuran_kas" value="Iuran kas per bulan (Rp)" />
                    <x-text-input id="iuran_kas" name="iuran_kas" type="number" min="0" step="1000"
                                  class="mt-1 block w-full" :value="old('iuran_kas', (int) $kelas->iuran_kas)" required />
                    <x-input-error :messages="$errors->get('iuran_kas')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.kelas.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>