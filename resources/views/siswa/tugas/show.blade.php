<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $tugas->judul }}</h2>
            <a href="{{ route('siswa.tugas.index') }}" class="text-sm text-gray-600 hover:underline">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3 text-sm text-gray-700">
                <p>Mata pelajaran:
                    <span class="font-semibold">{{ $tugas->guruMapel->mapel->nama }}</span>
                    ({{ $tugas->guruMapel->guru->nama }})
                </p>
                <p>Deadline (WIB):
                    <span class="font-semibold">{{ $tugas->deadline->format('d-m-Y H:i') }}</span>
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ $status['kelas'] }}">{{ $status['label'] }}</span>
                </p>

                @if ($tugas->deskripsi)
                    <div>
                        <p>Instruksi:</p>
                        <p class="mt-1 whitespace-pre-line text-gray-800">{{ $tugas->deskripsi }}</p>
                    </div>
                @endif

                @if ($tugas->file_path)
                    <p>
                        <a href="{{ route('siswa.tugas.lampiran', $tugas) }}" class="text-indigo-700 hover:underline">
                            Unduh lampiran soal
                        </a>
                    </p>
                @endif
            </div>

            {{-- Hasil penilaian --}}
            @if ($pengumpulan && $pengumpulan->nilai !== null)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-700 space-y-2">
                    <h3 class="text-sm font-semibold text-gray-900">Penilaian guru</h3>
                    <p>Nilai: <span class="text-lg font-bold">{{ rtrim(rtrim((string) $pengumpulan->nilai, '0'), '.') }}</span></p>
                    @if ($pengumpulan->catatan_guru)
                        <p>Catatan: <span class="text-gray-800">{{ $pengumpulan->catatan_guru }}</span></p>
                    @endif
                </div>
            @endif

            {{-- Jawaban saat ini --}}
            @if ($pengumpulan && $pengumpulan->submitted_at)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-700 space-y-2">
                    <h3 class="text-sm font-semibold text-gray-900">Jawaban Anda</h3>
                    <p class="text-xs text-gray-500">Dikumpulkan {{ $pengumpulan->submitted_at->format('d-m-Y H:i') }} WIB</p>
                    @if ($pengumpulan->file_path)
                        <p><a href="{{ route('siswa.tugas.jawaban', $tugas) }}" class="text-indigo-700 hover:underline">Unduh file jawaban</a></p>
                    @endif
                    @if ($pengumpulan->jawaban_teks)
                        <p class="whitespace-pre-line text-gray-800">{{ $pengumpulan->jawaban_teks }}</p>
                    @endif
                </div>
            @endif

            {{-- Form pengumpulan --}}
            @if ($bisaKumpul)
                <form method="POST" enctype="multipart/form-data" action="{{ route('siswa.tugas.kumpul', $tugas) }}"
                      class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <h3 class="text-sm font-semibold text-gray-900">
                        {{ $pengumpulan && $pengumpulan->submitted_at ? 'Kumpulkan ulang jawaban' : 'Kumpulkan jawaban' }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        Anda bisa mengumpulkan ulang sampai deadline. File baru menggantikan file sebelumnya.
                    </p>

                    <div>
                        <x-input-label for="file" value="File jawaban (maksimal 10 MB)" />
                        <input id="file" name="file" type="file"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                               class="mt-1 block w-full text-sm text-gray-700">
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jawaban_teks" value="Jawaban teks (opsional jika sudah mengunggah file)" />
                        <textarea id="jawaban_teks" name="jawaban_teks" rows="5" maxlength="10000"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('jawaban_teks', $pengumpulan?->jawaban_teks) }}</textarea>
                        <x-input-error :messages="$errors->get('jawaban_teks')" class="mt-2" />
                    </div>

                    <x-primary-button>Kumpulkan</x-primary-button>
                </form>
            @elseif ($pengumpulan && $pengumpulan->nilai !== null)
                <p class="text-xs text-gray-500">Tugas sudah dinilai, jawaban tidak bisa diubah lagi.</p>
            @else
                <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
                    Deadline sudah lewat, tugas ini ditutup.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>