<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rata-rata Nilai</h2>
            @if ($semester)
                <a href="{{ route('kepsek.nilai.pdf', ['semester_id' => $semester->id]) }}"
                    class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                    Unduh PDF
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('admin.partials.pesan')

            @if (!$semester)
                <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">Belum ada semester.</div>
            @else
                <form method="GET" class="flex items-center gap-3 text-sm">
                    <label for="semester_id" class="text-gray-600">Semester</label>
                    <select id="semester_id" name="semester_id" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach ($daftarSemester as $s)
                            <option value="{{ $s->id }}" @selected($s->id === $semester->id)>
                                {{ $s->nama }} {{ $s->tahunAjaran?->nama }}{{ $s->is_active ? ' (aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Rata-rata nilai per kelas</h3>
                    <div style="position:relative;height:260px;margin-top:12px"><canvas id="grafik-kelas"></canvas></div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Mata pelajaran</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Siswa dinilai</th>
                                <th class="px-4 py-3">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($baris as $b)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $b['kelas'] }}</td>
                                    <td class="px-4 py-3">{{ $b['mapel'] }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $b['guru'] }}</td>
                                    <td class="px-4 py-3">{{ $b['jumlah_dinilai'] }} / {{ $b['jumlah_siswa'] }}</td>
                                    <td class="px-4 py-3 font-bold">{{ \App\Support\Angka::nilai($b['rata_rata']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada penugasan guru pada
                                        tahun ajaran ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500">
                    Rata-rata dihitung dari nilai akhir tiap siswa (tugas dan ujian berbobot). Siswa yang belum bernilai
                    tidak ikut dihitung.
                </p>

                <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
                <script>
                    (function () {
                        if (!window.Chart) return;
                        const kelas = @json($perKelas->values());
                        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
                        new Chart(document.getElementById('grafik-kelas'), {
                            type: 'bar',
                            data: {
                                labels: kelas.map((x) => x.kelas),
                                datasets: [{ label: 'Rata-rata nilai', data: kelas.map((x) => x.rata_rata), backgroundColor: '#8745ad' }],
                            },
                            options: {
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true, max: 100 } },
                            },
                        });
                    })();
                </script>
            @endif
        </div>
    </div>
</x-app-layout>