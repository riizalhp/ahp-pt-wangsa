<x-layouts.app title="Laporan Hasil Penilaian Supplier">
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Laporan resmi pemeringkatan kelayakan supplier terbaik berdasarkan kalkulasi metode AHP.</p>
        
        <!-- Supervisor only action button (Req 10.4) -->
        @if(auth()->user()->role === 'supervisor')
            <div class="flex items-center gap-3">
                <a href="{{ route('supervisor.laporan.penilaian.cetak') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/15 transition-all">
                    <i class="fas fa-print"></i> Cetak PDF
                </a>
                
                @if(!$rankings->isEmpty())
                    <form action="{{ route('supervisor.laporan.penilaian.reset') }}" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin mereset semua hasil penilaian? Data peringkat akan dihapus dan Anda perlu melakukan perhitungan AHP ulang.');"
                          class="inline">
                        @csrf
                        <button type="submit"
                                style="background-color: #dc2626; color: white; border: 2px solid #991b1b;"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 shadow-md transition-all">
                            <i class="fas fa-redo"></i> Reset Penilaian
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Table (full width on mobile, 3/5 width on desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-1">
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
                                    <th class="pb-3">Produk</th>
                                    <th class="pb-3 text-right">Skor Akhir (AHP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-medium">
                                @foreach($rankings as $rank)
                                    @php
                                        // Get products from this supplier, filtered by selected products in session if available
                                        $selectedProductIds = session('ahp_selected_products', []);
                                        $allProducts = $rank->supplier->produk;
                                        if (!empty($selectedProductIds)) {
                                            $products = $allProducts->whereIn('id', $selectedProductIds);
                                        } else {
                                            $products = $allProducts;
                                        }
                                        $totalProductsCount = $products->count();
                                        $productsLimit = $products->take(3);
                                    @endphp
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
                                        <td class="py-3.5 text-xs text-slate-600">
                                            @if($productsLimit->isEmpty())
                                                <span class="italic text-slate-400">Belum ada produk</span>
                                            @else
                                                @foreach($productsLimit as $product)
                                                    <div class="mb-1">
                                                        <span class="font-semibold text-slate-700">{{ $product->nama }}</span>
                                                        @if($product->merk || $product->ukuran)
                                                            <span class="text-slate-400"> • </span>
                                                        @endif
                                                        @if($product->merk)
                                                            <span class="text-slate-500">{{ $product->merk }}</span>
                                                        @endif
                                                        @if($product->ukuran)
                                                            <span class="text-slate-400"> ({{ $product->ukuran }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if($totalProductsCount > 3)
                                                    <span class="text-slate-400 italic text-[10px]">+{{ $totalProductsCount - 3 }} produk lainnya</span>
                                                @endif
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

        <!-- Charts Column -->
        <div class="space-y-6">
            <!-- Donut Chart: Bobot Kriteria -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bobot Kriteria</h3>
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
                                    <span class="font-bold text-slate-500">{{ number_format(($kriteriaWeights[$k->id] ?? 0) * 100, 2) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Donut Chart: Bobot Global Subkriteria -->
            @if(!$rankings->isEmpty() && isset($subkriterias) && isset($globalSubkriteriaWeights))
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bobot Global Subkriteria</h3>
                    </div>
                    <div class="p-6 flex flex-col items-center justify-center">
                        <div class="w-full max-w-[200px] mb-4">
                            <canvas id="chartBobotSubkriteriaReport"></canvas>
                        </div>
                        
                        <!-- Table showing weights -->
                        <div class="w-full border-t border-slate-100 pt-4 space-y-2 max-h-64 overflow-y-auto">
                            @foreach($subkriterias as $index => $sk)
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded bg-teal" style="background-color: {{ ['#009688', '#26A69A', '#4DB6AC', '#80CBC4', '#00695C', '#00897B', '#26A69A', '#4CAF50', '#66BB6A', '#81C784', '#FF9800', '#FFA726', '#FFB74D', '#9C27B0', '#AB47BC', '#BA68C8', '#2196F3', '#42A5F5', '#64B5F6', '#E91E63'][$index % 20] ?? '#009688' }}"></span>
                                        <span class="font-bold text-slate-700">[{{ $sk->kriteria->kode }}{{ $sk->kode }}] {{ $sk->nama }}</span>
                                    </div>
                                    <span class="font-bold text-slate-500">{{ number_format(($globalSubkriteriaWeights[$sk->id] ?? 0) * 100, 2) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart rendering logic -->
    @if(!$rankings->isEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Kriteria Chart
                const kriteriaLabels = {!! json_encode($kriterias->pluck('nama')->toArray()) !!};
                const kriteriaData = {!! json_encode(array_values($kriteriaWeights)) !!};
                
                const ctxKriteria = document.getElementById('chartBobotKriteriaReport').getContext('2d');
                new Chart(ctxKriteria, {
                    type: 'doughnut',
                    data: {
                        labels: kriteriaLabels,
                        datasets: [{
                            data: kriteriaData,
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

                @if(isset($subkriterias) && isset($globalSubkriteriaWeights))
                // Subkriteria Chart
                const subkriteriaLabels = {!! json_encode($subkriterias->map(function($sk) {
                    return '[' . $sk->kriteria->kode . $sk->kode . '] ' . $sk->nama;
                })->toArray()) !!};
                const subkriteriaData = {!! json_encode(array_values($globalSubkriteriaWeights)) !!};
                
                const ctxSubkriteria = document.getElementById('chartBobotSubkriteriaReport').getContext('2d');
                new Chart(ctxSubkriteria, {
                    type: 'doughnut',
                    data: {
                        labels: subkriteriaLabels,
                        datasets: [{
                            data: subkriteriaData,
                            backgroundColor: [
                                '#009688', '#26A69A', '#4DB6AC', '#80CBC4', '#00695C', '#00897B', '#26A69A', 
                                '#4CAF50', '#66BB6A', '#81C784', '#FF9800', '#FFA726', '#FFB74D', 
                                '#9C27B0', '#AB47BC', '#BA68C8', '#2196F3', '#42A5F5', '#64B5F6', '#E91E63'
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
                @endif
            });
        </script>
    @endif
</x-layouts.app>
