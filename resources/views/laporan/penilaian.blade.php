<x-layouts.app title="Laporan Hasil Penilaian Supplier">
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Laporan resmi pemeringkatan kelayakan supplier terbaik berdasarkan kalkulasi metode AHP.</p>
        
        <!-- Supervisor only action button (Req 10.4) -->
        @if(auth()->user()->role === 'supervisor')
            <div class="flex items-center gap-2">
                <a href="{{ route('supervisor.laporan.penilaian.pdf') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/15 transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                </a>
                <button onclick="alert('Laporan penilaian berhasil diajukan ke jajaran manajemen.')" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/15 transition-all">
                    <i class="fas fa-paper-plane"></i> Ajukan Laporan
                </button>
            </div>
        @endif
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Table (2/3 width) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide">Peringkat Kelayakan Supplier Terpilih</h3>
                <span class="inline-flex items-center gap-1 text-slate-400 text-xs font-medium">
                    <i class="fas fa-clock"></i> Terakhir Diupdate: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }}
                </span>
            </div>
            
            <div class="p-6">
                @if($rankings->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Data pemeringkatan supplier belum tersedia. Lakukan kalkulasi AHP di menu perhitungan.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-150">
                            <thead>
                                <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="pb-3">Peringkat</th>
                                    <th class="pb-3">Kode</th>
                                    <th class="pb-3">Nama Supplier</th>
                                    <th class="pb-3 text-right">Skor Akhir (AHP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-medium">
                                @foreach($rankings as $rank)
                                    <tr class="hover:bg-slate-50/40">
                                        <td class="py-3.5">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl font-extrabold text-xs
                                                {{ $rank->ranking == 1 ? 'bg-teal text-white shadow shadow-teal/20 scale-105' : 'bg-slate-150 text-slate-600' }}">
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
                                        <td class="py-3.5 text-right font-extrabold text-teal">
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

        <!-- Donut Chart: Bobot Kriteria (1/3 width) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col h-fit">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bobot Kriteria AHP</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center">
                @if($rankings->isEmpty())
                    <p class="text-xs text-slate-400 italic">Data belum tersedia.</p>
                @else
                    <div class="w-full max-w-[200px] mb-4">
                        <canvas id="chartBobotKriteriaReport"></canvas>
                    </div>
                    
                    <!-- Table showing weights -->
                    <div class="w-full border-t border-slate-100 pt-4 space-y-2">
                        @foreach($kriterias as $index => $k)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded bg-teal" style="background-color: {{ ['#009688', '#00695C', '#4CAF50', '#FF9800', '#9C27B0'][$index] ?? '#009688' }}"></span>
                                    <span class="font-bold text-slate-700">[{{ $k->kode }}] {{ $k->nama }}</span>
                                </div>
                                <span class="font-bold text-slate-500">{{ number_format(($kriteriaWeights[$index] ?? 0) * 100, 2) }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Chart rendering logic -->
    @if(!$rankings->isEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const kriteriaLabels = {!! json_encode($kriterias->pluck('nama')->toArray()) !!};
                const kriteriaWeights = {!! json_encode($kriteriaWeights) !!};
                
                const ctxDonut = document.getElementById('chartBobotKriteriaReport').getContext('2d');
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
                                display: false
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
            });
        </script>
    @endif
</x-layouts.app>
