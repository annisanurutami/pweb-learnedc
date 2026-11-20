<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $judul }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>Selamat datang, <span class="font-semibold">{{ auth()->user()->name }}</span>.</p>
                    <p class="text-sm text-gray-500 mt-1">
                        Peran Anda: {{ auth()->user()->getRoleNames()->implode(', ') }}
                    </p>

                    @role('super_admin')
                        <div class="mt-4 flex flex-wrap gap-2 text-sm">
                            <a href="{{ route('admin.tahun-ajaran.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Tahun
                                Ajaran</a>
                            <a href="{{ route('admin.semester.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Semester</a>
                            <a href="{{ route('admin.hari.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Hari
                                Aktif</a>
                            <a href="{{ route('admin.kelas.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Kelas</a>
                            <a href="{{ route('admin.mata-pelajaran.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Mata
                                Pelajaran</a>
                            <a href="{{ route('admin.penugasan.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Penugasan
                                Guru</a>
                            <a href="{{ route('admin.guru.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Akun
                                Guru</a>
                            <a href="{{ route('admin.siswa.index') }}"
                                class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Akun
                                Siswa</a>
                        </div>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</x-app-layout>