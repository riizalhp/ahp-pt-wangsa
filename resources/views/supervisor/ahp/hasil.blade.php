<x-layouts.app title="Hasil Perhitungan AHP">
    <!-- Stepper Navigation -->
    <x-ui.stepper active="hasil" />

    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="mt-6 flex flex-col gap-6">
        <!-- 1. Ranking Table & Charts Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Table Panel (2/3 width) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-2 flex flex-col justify-between">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800 tracking-wide">Peringkat Kelayakan Supplier Terpilih</h3>
                </div>
                <div class="p-6 flex-1">
                    @if($rankings->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-center h-full">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>
                            <p class="text-sm text-slate-500 font-medium">Belum ada hasil ranking yang tersimpan. Lakukan perbandingan kriteria, subkriteria, dan supplier terlebih dahulu.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-150">
                                <thead>
                                    <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        <th class="pb-3">Peringkat</th>
                                        <th class="pb-3">Kode</th>
                                        <th class="pb-3">Nama Supplier</th>
                                        <th class="pb-3 text-right">Nilai Akhir (Skor AHP)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm font-medium">
                                    @foreach($rankings as $rank)
                                        <tr class="hover:bg-slate-50/40">
                                            <td class="py-3.5">
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl font-extrabold text-xs
                                                    {{ $rank->ranking == 1 ? 'bg-teal text-white shadow-md shadow-teal/20 scale-105 border border-teal' : '' }}
                                                    {{ $rank->ranking == 2 ? 'bg-slate-100 text-slate-700' : '' }}
                                                    {{ $rank->ranking == 3 ? 'bg-orange-50 text-orange-700 border border-orange-100' : '' }}
                                                    {{ $rank->ranking > 3 ? 'text-slate-400' : '' }}">
                                                    {{ $rank->ranking }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 font-bold text-slate-500">{{ $rank->supplier->kode }}</td>
                                            <td class="py-3.5 font-bold text-slate-800">
                                                {{ $rank->supplier->nama }}
                                                @if($rank->ranking == 1)
                                                    <span class="ml-2 inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <i class="fas fa-crown text-[8px]"></i> Rekomendasi
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 text-right font-extrabold text-teal text-base">
                                                {{ number_format($rank->nilai_akhir, 5) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Donut Chart: Kriteria Weights (1/3 width) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bobot Kepentingan Kriteria</h3>
                </div>
                <div class="p-6 flex-1 flex flex-col items-center justify-center">
                    @if($rankings->isEmpty())
                        <p class="text-xs text-slate-400 italic">Data belum tersedia.</p>
                    @else
                        <div class="w-full max-w-[220px]">
                            <canvas id="chartBobotKriteria"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Bar Chart: Supplier Score Rankings -->
        @if(!$rankings->isEmpty())
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide mb-6">Visualisasi Perbandingan Skor Akhir Supplier</h3>
                <div class="w-full h-80">
                    <canvas id="chartRanking"></canvas>
                </div>
            </div>
        @endif
    </div>

    <!-- Chart rendering logic -->
    @if(!$rankings->isEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Donut Chart: Bobot Kriteria
                const kriteriaLabels = {!! json_encode($kriterias->pluck('nama')->toArray()) !!};
                const kriteriaWeights = {!! json_encode($kriteriaWeights) !!};
                
                const ctxDonut = document.getElementById('chartBobotKriteria').getContext('2d');
                new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: kriteriaLabels,
                        datasets: [{
                            data: kriteriaWeights,
                            backgroundColor: [
                                '#009688', // teal
                                '#00695C', // teal-dark
                                '#4CAF50', // green
                                '#FF9800', // orange
                                '#9C27B0'  // purple
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    font: {
                                        size: 10,
                                        weight: 'semibold'
                                    },
                                    padding: 15
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let val = context.raw || 0;
                                        return context.label + ': ' + (val * 100).toFixed(2) + '%';
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });

                // 2. Bar Chart: Supplier Score Rankings
                const supplierLabels = {!! json_encode($rankings->map(fn($r) => $r->supplier->nama)->toArray()) !!};
                const supplierScores = {!! json_encode($rankings->pluck('nilai_akhir')->toArray()) !!};

                const ctxBar = document.getElementById('chartRanking').getContext('2d');
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: supplierLabels,
                        datasets: [{
                            label: 'Skor Akhir Kelayakan',
                            data: supplierScores,
                            backgroundColor: supplierScores.map((_, i) => i === 0 ? '#009688' : '#e2e8f0'), // Highlight winner with teal
                            borderRadius: 8,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 1.0,
                                grid: {
                                    color: '#f1f5f9'
                                },
                                border: {
                                    dash: [5, 5]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-layouts.app>
