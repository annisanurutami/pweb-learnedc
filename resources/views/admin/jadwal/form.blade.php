<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $jadwal->exists ? 'Ubah Jadwal' : 'Tambah Jadwal' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ $jadwal->exists ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @csrf
                @if ($jadwal->exists)
                    @method('PUT')
                @endif

                <p class="text-sm text-gray-600">
                    Semester aktif:
                    <span class="font-semibold">{{ $semester->nama }} {{ $semester->tahunAjaran?->nama }}</span>
                </p>

                @if ($jadwal->exists)
                    <div class="text-sm text-gray-700 space-y-1">
                        <p>Kelas: <span class="font-semibold">{{ $jadwal->guruMapel->kelas->nama_kelas }}</span></p>
                        <p>Mata pelajaran: <span class="font-semibold">{{ $jadwal->guruMapel->mapel->nama }}</span></p>
                        <p>Guru: <span class="font-semibold">{{ $jadwal->guruMapel->guru->nama }}</span></p>
                    </div>
                @else
                    <div>
                        <x-input-label for="guru_mapel_id" value="Penugasan (kelas - mapel - guru)" />
                        <select id="guru_mapel_id" name="guru_mapel_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Pilih penugasan</option>
                            @foreach ($daftarPenugasan as $p)
                                <option value="{{ $p->id }}" @selected((int) old('guru_mapel_id', $jadwal->guru_mapel_id) === $p->id)>
                                    {{ $p->kelas->nama_kelas }} - {{ $p->mapel->nama }} - {{ $p->guru->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('guru_mapel_id')" class="mt-2" />
                    </div>
                @endif

                <div>
                    <x-input-label for="hari_id" value="Hari" />
                    <select id="hari_id" name="hari_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih hari</option>
                        @foreach ($daftarHari as $h)
                            <option value="{{ $h->id }}" @selected((int) old('hari_id', $jadwal->hari_id) === (int) $h->id)>
                                {{ $h->nama_hari }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('hari_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="jam_mulai" value="Jam mulai" />
                        <x-text-input id="jam_mulai" name="jam_mulai" type="time" class="mt-1 block w-full"
                                      :value="old('jam_mulai', $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '')" required />
                        <x-input-error :messages="$errors->get('jam_mulai')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="jam_selesai" value="Jam selesai" />
                        <x-text-input id="jam_selesai" name="jam_selesai" type="time" class="mt-1 block w-full"
                                      :value="old('jam_selesai', $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '')" required />
                        <x-input-error :messages="$errors->get('jam_selesai')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.jadwal.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>