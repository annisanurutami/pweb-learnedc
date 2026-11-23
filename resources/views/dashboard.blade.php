<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $judul }}</h2>
    </x-slot>

    <section class="welcome">
        <div>
            <span class="eyebrow">{{ $eyebrow }}</span>
            <h2>Selamat datang, {{ auth()->user()->name }}</h2>
            <p>
                @if ($semester)
                    Semester aktif: {{ $semester->nama }} {{ $semester->tahunAjaran?->nama }}.
                @else
                    Belum ada semester aktif.
                @endif
            </p>
        </div>
        <div class="welcome-icon">✦</div>
    </section>

    @if (count($statistik) > 0)
        <section class="stat-grid">
            @foreach ($statistik as $stat)
                <article class="stat-card">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['nilai'] }}</strong>
                    <small>{{ $stat['keterangan'] }}</small>
                </article>
            @endforeach
        </section>
    @endif
</x-app-layout>
