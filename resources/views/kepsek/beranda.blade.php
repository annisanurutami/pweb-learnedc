<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Kepala Sekolah</h2>
    </x-slot>

    <section class="welcome">
        <div>
            <span class="eyebrow">PORTAL KEPALA SEKOLAH</span>
            <h2>Selamat datang, {{ auth()->user()->name }}</h2>
            <p>
                @if ($semester)
                    Ringkasan keaktifan semester {{ $semester->nama }} {{ $semester->tahunAjaran?->nama }}.
                @else
                    Belum ada semester.
                @endif
            </p>
        </div>
        <div class="welcome-icon">✦</div>
    </section>

    @if ($data)
    @php($r = $data['ringkasan'])

    <section class="stat-grid">
        <article class="stat-card">
            <span>Siswa aktif</span>
            <strong>{{ $r['siswa_aktif'] }} / {{ $r['total_siswa'] }}</strong>
            <small>Login dalam 7 hari terakhir</small>
        </article>
        <article class="stat-card">
            <span>Guru aktif</span>
            <strong>{{ $r['guru_aktif'] }} / {{ $r['total_guru'] }}</strong>
            <small>Login dalam 7 hari terakhir</small>
        </article>
        <article class="stat-card">
            <span>Tugas dan materi</span>
            <strong>{{ $r['tugas_dibuat'] }} / {{ $r['materi_dibuat'] }}</strong>
            <small>Tugas dibuat / materi diunggah</small>
        </article>
        <article class="stat-card">
            <span>Pengumpulan tugas</span>
            <strong>{{ $r['pengumpulan'] }}</strong>
            <small>Pada semester ini</small>
        </article>
        <article class="stat-card">
            <span>Kehadiran siswa</span>
            <strong>{{ $r['kehadiran'] !== null ? \App\Support\Angka::nilai($r['kehadiran'], 1) . '%' : '-' }}</strong>
            <small>Rata-rata seluruh kelas</small>
        </article>
    </section>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:16px;margin-top:16px">
        <div class="bg-white shadow-sm sm:rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900">Login 14 hari terakhir</h3>
            <div style="position:relative;height:260px;margin-top:12px"><canvas id="grafik-login"></canvas></div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-900">Pengumpulan tugas 14 hari terakhir</h3>
            <div style="position:relative;height:260px;margin-top:12px"><canvas id="grafik-pengumpulan"></canvas></div>
        </div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-5" style="margin-top:16px">
        <h3 class="text-sm font-semibold text-gray-900">Aktivitas guru semester ini</h3>
        <p class="text-xs text-gray-500">Materi diunggah, tugas dibuat, dan pertemuan yang diabsen.</p>
        <div style="position:relative;height:{{ max(220, $data['guru']->count() * 48 + 70) }}px;margin-top:12px">
            <canvas id="grafik-guru"></canvas>
        </div>
    </div>

    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            if (!window.Chart) return;

            const g = @json($data['grafik']);
            const guru = @json($data['guru']->values());
            const ungu = '#8745ad', muda = '#c98bdc', emas = '#e0a93b';
            const sumbuY = { beginAtZero: true, ticks: { precision: 0 } };

            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

            new Chart(document.getElementById('grafik-login'), {
                type: 'line',
                data: {
                    labels: g.tanggal,
                    datasets: [
                        { label: 'Guru', data: g.login_guru, borderColor: ungu, backgroundColor: ungu, tension: .3 },
                        { label: 'Siswa', data: g.login_siswa, borderColor: emas, backgroundColor: emas, tension: .3 },
                    ],
                },
                options: { maintainAspectRatio: false, scales: { y: sumbuY } },
            });

            new Chart(document.getElementById('grafik-pengumpulan'), {
                type: 'bar',
                data: { labels: g.tanggal, datasets: [{ label: 'Pengumpulan', data: g.pengumpulan, backgroundColor: muda }] },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: sumbuY } },
            });

            new Chart(document.getElementById('grafik-guru'), {
                type: 'bar',
                data: {
                    labels: guru.map((x) => x.nama),
                    datasets: [
                        { label: 'Materi', data: guru.map((x) => x.materi), backgroundColor: ungu },
                        { label: 'Tugas', data: guru.map((x) => x.tugas), backgroundColor: muda },
                        { label: 'Pertemuan diabsen', data: guru.map((x) => x.absensi), backgroundColor: emas },
                    ],
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } },
                },
            });
        })();
    </script>
    @endif
</x-app-layout>