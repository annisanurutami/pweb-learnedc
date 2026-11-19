<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hari Aktif Belajar</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            <form method="POST" action="{{ route('admin.hari.update') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-600">Centang hari yang dipakai untuk kegiatan belajar.</p>

                @foreach ($daftar as $hari)
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="aktif[]" value="{{ $hari->id }}"
                               class="rounded border-gray-300" @checked($hari->is_active)>
                        <span>{{ $hari->nama_hari }}</span>
                    </label>
                @endforeach

                <x-primary-button>Simpan</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>