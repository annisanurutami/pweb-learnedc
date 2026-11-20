<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $materi->exists ? 'Ubah Materi' : 'Tambah Materi' }} - {{ $penugasan->mapel->nama }} (Kelas {{ $penugasan->kelas->nama_kelas }})
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data"
                  action="{{ $materi->exists
                      ? route('guru.materi.update', ['penugasan' => $penugasan, 'materi' => $materi])
                      : route('guru.materi.store', $penugasan) }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($materi->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="judul" value="Judul materi" />
                    <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full"
                                  :value="old('judul', $materi->judul)" required maxlength="200" />
                    <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="deskripsi" value="Deskripsi (opsional)" />
                    <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('deskripsi', $materi->deskripsi) }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="file" value="File materi (PDF, DOC, DOCX, PPT, PPTX, maksimal 10 MB)" />
                    <input id="file" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx"
                           class="mt-1 block w-full text-sm text-gray-700">
                    @if ($materi->exists && $materi->file_path)
                        <p class="mt-1 text-xs text-gray-500">
                            File saat ini tersimpan
                            (<a href="{{ route('guru.materi.unduh', ['penugasan' => $penugasan, 'materi' => $materi]) }}"
                                class="text-indigo-700 hover:underline">unduh</a>).
                            Pilih file baru hanya jika ingin menggantinya.
                        </p>
                    @endif
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="link_url" value="Link video (opsional jika sudah ada file)" />
                    <x-text-input id="link_url" name="link_url" type="text" class="mt-1 block w-full"
                                  placeholder="https://www.youtube.com/watch?v=..."
                                  :value="old('link_url', $materi->link_url)" maxlength="500" />
                    <x-input-error :messages="$errors->get('link_url')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('guru.materi.index', $penugasan) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>