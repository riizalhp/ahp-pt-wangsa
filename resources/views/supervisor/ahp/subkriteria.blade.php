<x-layouts.app title="Perbandingan Berpasangan Subkriteria">
    <!-- Stepper Navigation -->
    <x-ui.stepper active="subkriteria" />

    <div class="mt-6 flex flex-col lg:flex-row gap-6">
        <!-- Main Form (2/3 width) -->
        <div class="flex-1 space-y-8">
            @if($kriterias->isEmpty())
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Tidak ada kriteria yang memiliki lebih dari satu subkriteria untuk dibandingkan.</p>
                    <a href="{{ route('supervisor.ahp.supplier') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold shadow-md hover:bg-teal-dark">
                        Lanjutkan ke Supplier <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @else
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 tracking-wide mb-4">Pairwise Comparison Subkriteria</h3>
                    <p class="text-xs text-slate-500 mb-6">Bandingkan tingkat kepentingan relatif antar subkriteria di dalam kriteria induk yang sama.</p>

                    <form action="{{ route('supervisor.ahp.subkriteria') }}" method="POST" class="space-y-10">
                        @csrf
                        
                        @foreach($kriterias as $kCriteria)
                            @php
                                $subs = $kCriteria->subkriteria;
                                $n = $subs->count();
                                
                                // Generate pairs
                                $kPairs = [];
                                for ($i = 0; $i < $n; $i++) {
                                    for ($j = $i + 1; $j < $n; $j++) {
                                        $kPairs[] = [
                                            'a' => $subs[$i],
                                            'b' => $subs[$j]
                                        ];
                                    }
                                }
                            @endphp

                            <div class="space-y-4 border border-slate-200/80 rounded-2xl p-6 bg-slate-50/10 shadow-sm">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal/15 text-teal-dark uppercase">{{ $kCriteria->kode }}</span>
                                        <span class="text-xs font-extrabold text-slate-800">{{ $kCriteria->nama }}</span>
                                    </div>
                                    @if(isset($kriteriaCrs[$kCriteria->id]))
                                        @php $crInfo = $kriteriaCrs[$kCriteria->id]; @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border 
                                            {{ $crInfo['consistent'] ? 'bg-teal-50 text-teal-dark border-teal-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                                            CR: {{ number_format($crInfo['cr'], 4) }} ({{ $crInfo['consistent'] ? 'Konsisten' : 'Inkonsisten' }})
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-6">
                                    @foreach($kPairs as $pair)
                                        @php
                                            $a = $pair['a'];
                                            $b = $pair['b'];
                                            $key = "{$kCriteria->id}-{$a->id}-{$b->id}";
                                            $currentVal = $existingIndexed[$key] ?? 1.0;
                                        @endphp
                                        
                                        <div class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm flex flex-col gap-3"
                                             x-data="{ activeVal: '{{ $currentVal }}' }">
                                            
                                            <!-- Sub Header -->
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="font-bold text-slate-700">{{ $a->nama }} ({{ $a->kode }})</span>
                                                <span class="text-slate-400 font-semibold vs">VS</span>
                                                <span class="font-bold text-slate-700 text-right">{{ $b->nama }} ({{ $b->kode }})</span>
                                            </div>

                                            <!-- Saaty Selector Row -->
                                            <div class="w-full flex items-center justify-between gap-0.5 bg-slate-50/70 p-1.5 rounded-lg border border-slate-100 overflow-x-auto">
                                                <!-- LEFT SIDE -->
                                                <div class="flex items-center gap-0.5">
                                                    @foreach([9, 8, 7, 6, 5, 4, 3, 2] as $val)
                                                        <label class="flex flex-col items-center">
                                                            <input type="radio" name="nilai[{{ $kCriteria->id }}][{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
                                                                   x-model="activeVal" class="sr-only">
                                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded cursor-pointer text-[10px] font-bold transition-all
                                                                x-cloak"
                                                                  :class="activeVal == '{{ $val }}' ? 'bg-teal text-white shadow shadow-teal/10 scale-105' : 'text-slate-400 hover:bg-slate-200/50 hover:text-slate-600'">
                                                                {{ $val }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                <!-- MIDDLE -->
                                                <label class="flex flex-col items-center">
                                                    <input type="radio" name="nilai[{{ $kCriteria->id }}][{{ $a->id }}][{{ $b->id }}]" value="1" 
                                                           x-model="activeVal" class="sr-only">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded cursor-pointer text-[10px] font-bold border border-slate-200 transition-all"
                                                          :class="activeVal == '1' ? 'bg-teal text-white border-teal shadow shadow-teal/10 scale-105' : 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-600'">
                                                        1
                                                    </span>
                                                </label>

                                                <!-- RIGHT SIDE -->
                                                <div class="flex items-center gap-0.5">
                                                    @foreach([2 => 0.5, 3 => 0.333333, 4 => 0.25, 5 => 0.2, 6 => 0.166667, 7 => 0.142857, 8 => 0.125, 9 => 0.111111] as $label => $val)
                                                        <label class="flex flex-col items-center">
                                                            <input type="radio" name="nilai[{{ $kCriteria->id }}][{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
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
                            <a href="{{ route('supervisor.ahp.kriteria') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition-colors">
                                <i class="fas fa-arrow-left mr-1"></i> Langkah Sebelumnya
                            </a>
                            <button type="submit" class="px-6 py-3 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-lg shadow-teal/25 hover:shadow-teal/35 hover:-translate-y-0.5 transition-all">
                                Simpan & Lanjutkan <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="w-full lg:w-96 space-y-6">
            <!-- Explain Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">Konsistensi Subkriteria</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-3">
                    Setiap grup subkriteria yang dibandingkan harus bernilai konsisten (**CR &le; 0.10**). 
                </p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Sistem akan memvalidasi rasio konsistensi per kriteria induk secara individual. Apabila ada salah satu induk kriteria yang perbandingannya tidak konsisten, Anda harus menyesuaikan nilainya kembali.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
