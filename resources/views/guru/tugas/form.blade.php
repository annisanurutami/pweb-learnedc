<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $tugas->exists ? 'Ubah Tugas' : 'Tambah Tugas' }} - {{ $penugasan->mapel->nama }} (Kelas {{ $penugasan->kelas->nama_kelas }})
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data"
                  action="{{ $tugas->exists
                      ? route('guru.tugas.update', ['penugasan' => $penugasan, 'tugas' => $tugas])
                      : route('guru.tugas.store', $penugasan) }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($tugas->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="judul" value="Judul tugas" />
                    <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full"
                                  :value="old('judul', $tugas->judul)" required maxlength="200" />
                    <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="deskripsi" value="Deskripsi / instruksi (opsional)" />
                    <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="5000"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="deadline" value="Deadline (WIB)" />
                    <x-text-input id="deadline" name="deadline" type="datetime-local" class="mt-1 block w-full"
                                  :value="old('deadline', $tugas->deadline?->format('Y-m-d\TH:i'))" required />
                    <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="lampiran" value="Lampiran (opsional, maksimal 10 MB)" />
                    <input id="lampiran" name="lampiran" type="file"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png"
                           class="mt-1 block w-full text-sm text-gray-700">
                    @if ($tugas->exists && $tugas->file_path)
                        <p class="mt-1 text-xs text-gray-500">
                            Lampiran saat ini tersimpan
                            (<a href="{{ route('guru.tugas.lampiran', ['penugasan' => $penugasan, 'tugas' => $tugas]) }}"
                                class="text-indigo-700 hover:underline">unduh</a>).
                            Pilih file baru hanya jika ingin menggantinya.
                        </p>
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="hapus_lampiran" value="1" class="rounded border-gray-300">
                            Hapus lampiran saat ini
                        </label>
                    @endif
                    <x-input-error :messages="$errors->get('lampiran')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('guru.tugas.index', $penugasan) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>