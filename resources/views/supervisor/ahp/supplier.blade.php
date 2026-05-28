<x-layouts.app title="Perbandingan Berpasangan Supplier">
    <!-- Stepper Navigation -->
    <x-ui.stepper active="supplier" />

    <div class="mt-6 flex flex-col gap-6">
        <!-- 1. Reference Panel: Supplier Actual Data Rekap (Req 5.2) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal/15 text-teal">
                    <i class="fas fa-database text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 tracking-wide">Panel Referensi Kinerja Aktual Supplier</h3>
            </div>
            
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Tabel di bawah menampilkan ringkasan data pengadaan riil (aktual) dari logistik untuk membantu pertimbangan subjektif Anda. 
                <span class="text-teal font-semibold font-sans">Perbandingan Saaty di bawah tidak di-autofill; nilai pairwise tetap murni berdasarkan judgement Anda.</span>
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="px-4 py-3 rounded-l-xl">Kode</th>
                            <th class="px-4 py-3">Nama Supplier</th>
                            <th class="px-4 py-3 text-center">Total Persen Cacat</th>
                            <th class="px-4 py-3 text-center">Total Persen Terlambat</th>
                            <th class="px-4 py-3 text-center rounded-r-xl">Rata-rata Terlambat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($suppliers as $supplier)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-semibold text-slate-500">{{ $supplier->kode }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $supplier->nama }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                        {{ number_format($supplier->total_persen_cacat, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                        {{ number_format($supplier->total_persen_keterlambatan, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-800 font-bold">
                                    {{ number_format($supplier->mean_hari_keterlambatan, 1) }} hari
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Form & Pairwise Comparison Section -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide mb-4">Form Input Perbandingan Supplier</h3>
            <p class="text-xs text-slate-500 mb-6">Bandingkan performa relatif supplier untuk masing-masing subkriteria yang tersedia.</p>

            <form action="{{ route('supervisor.ahp.supplier') }}" method="POST" class="space-y-10">
                @csrf
                
                @php
                    $nSuppliers = $suppliers->count();
                    $supplierIds = $suppliers->pluck('id')->toArray();
                    
                    // Generate pairs
                    $supplierPairs = [];
                    for ($i = 0; $i < $nSuppliers; $i++) {
                        for ($j = $i + 1; $j < $nSuppliers; $j++) {
                            $supplierPairs[] = [
                                'a' => $suppliers[$i],
                                'b' => $suppliers[$j]
                            ];
                        }
                    }
                @endphp

                @foreach($subkriterias as $sub)
                    <div class="space-y-4 border border-slate-200/80 rounded-2xl p-6 bg-slate-50/10 shadow-sm">
                        <!-- Group Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 uppercase">{{ $sub->kriteria->kode }}</span>
                                <span class="text-xs font-bold text-slate-500">{{ $sub->kriteria->nama }}</span>
                                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                                <span class="text-xs font-extrabold text-slate-800">{{ $sub->nama }} ({{ $sub->kode }})</span>
                            </div>
                            @if(isset($subkriteriaCrs[$sub->id]))
                                @php $crInfo = $subkriteriaCrs[$sub->id]; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border 
                                    {{ $crInfo['consistent'] ? 'bg-teal-50 text-teal-dark border-teal-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                                    CR: {{ number_format($crInfo['cr'], 4) }} ({{ $crInfo['consistent'] ? 'Konsisten' : 'Inkonsisten' }})
                                </span>
                            @endif
                        </div>

                        <!-- Pairs inside subcriteria -->
                        <div class="space-y-6">
                            @foreach($supplierPairs as $pair)
                                @php
                                    $a = $pair['a'];
                                    $b = $pair['b'];
                                    $key = "{$sub->id}-{$a->id}-{$b->id}";
                                    $currentVal = $existingIndexed[$key] ?? 1.0;
                                @endphp
                                
                                <div class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm flex flex-col gap-3"
                                     x-data="{ activeVal: '{{ $currentVal }}' }">
                                    
                                    <!-- Pair Name Label -->
                                    <div class="flex justify-between items-center text-xs">
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-semibold block mb-0.5">Supplier A</span>
                                            <span class="font-bold text-slate-800">{{ $a->nama }} ({{ $a->kode }})</span>
                                        </div>
                                        <span class="text-slate-300 font-extrabold text-sm uppercase">VS</span>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400 font-semibold block mb-0.5">Supplier B</span>
                                            <span class="font-bold text-slate-800">{{ $b->nama }} ({{ $b->kode }})</span>
                                        </div>
                                    </div>

                                    <!-- Saaty Scales Radio Buttons Row -->
                                    <div class="w-full flex items-center justify-between gap-0.5 bg-slate-50/70 p-1.5 rounded-lg border border-slate-100 overflow-x-auto">
                                        <!-- LEFT SIDE: A > B -->
                                        <div class="flex items-center gap-0.5">
                                            @foreach([9, 8, 7, 6, 5, 4, 3, 2] as $val)
                                                <label class="flex flex-col items-center">
                                                    <input type="radio" name="nilai[{{ $sub->id }}][{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
                                                           x-model="activeVal" class="sr-only">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded cursor-pointer text-[10px] font-bold transition-all"
                                                          :class="activeVal == '{{ $val }}' ? 'bg-teal text-white shadow shadow-teal/10 scale-105' : 'text-slate-400 hover:bg-slate-200/50 hover:text-slate-600'">
                                                        {{ $val }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <!-- MIDDLE: Equal -->
                                        <label class="flex flex-col items-center">
                                            <input type="radio" name="nilai[{{ $sub->id }}][{{ $a->id }}][{{ $b->id }}]" value="1" 
                                                   x-model="activeVal" class="sr-only">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded cursor-pointer text-[10px] font-bold border border-slate-200 transition-all"
                                                  :class="activeVal == '1' ? 'bg-teal text-white border-teal shadow shadow-teal/10 scale-105' : 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-600'">
                                                1
                                            </span>
                                        </label>

                                        <!-- RIGHT SIDE: B > A -->
                                        <div class="flex items-center gap-0.5">
                                            @foreach([2 => 0.5, 3 => 0.333333, 4 => 0.25, 5 => 0.2, 6 => 0.166667, 7 => 0.142857, 8 => 0.125, 9 => 0.111111] as $label => $val)
                                                <label class="flex flex-col items-center">
                                                    <input type="radio" name="nilai[{{ $sub->id }}][{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
                                                           x-model="activeVal" class="sr-only">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded cursor-pointer text-[10px] font-bold transition-all"
                                                          :class="activeVal == '{{ $val }}' || (parseFloat(activeVal).toFixed(3) == '{{ round($val, 3) }}') ? 'bg-teal text-white shadow shadow-teal/10 scale-105' : 'text-slate-400 hover:bg-slate-200/50 hover:text-slate-600'">
                                                        {{ $label }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('supervisor.ahp.subkriteria') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Langkah Sebelumnya
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-lg shadow-teal/25 hover:shadow-teal/35 hover:-translate-y-0.5 transition-all">
                        Hitung & Lihat Ranking <i class="fas fa-calculator ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
