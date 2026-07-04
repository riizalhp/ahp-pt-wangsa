<x-layouts.app title="Perbandingan Berpasangan Kriteria">
    <!-- Stepper Navigation -->
    <x-ui.stepper active="kriteria" />

    <div class="mt-6 flex flex-col lg:flex-row gap-6">
        <!-- Main Form (2/3 width) -->
        <div class="flex-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide mb-4">Pairwise Comparison Kriteria Utama</h3>
                <p class="text-xs text-slate-500 mb-6">Tentukan tingkat kepentingan relatif antar kriteria. Pilih nilai ke arah kriteria yang lebih penting.</p>

                <form action="{{ route('supervisor.ahp.kriteria') }}" method="POST" class="space-y-8">
                    @csrf
                    
                    @foreach($pairs as $index => $pair)
                        @php
                            $a = $pair['a'];
                            $b = $pair['b'];
                            $key = "{$a->id}-{$b->id}";
                            $currentVal = $existingIndexed[$key] ?? 1.0;
                        @endphp
                        
                        <div class="p-6 rounded-2xl border border-slate-200/60 bg-slate-50/30 flex flex-col gap-4 hover:border-teal/30 hover:bg-white transition-all duration-200" 
                             x-data="{ activeVal: '{{ $currentVal }}' }">
                            
                            <!-- Header showing the two items being compared -->
                            <div class="flex justify-between items-center px-2">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Kriteria A</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $a->nama }} ({{ $a->kode }})</span>
                                </div>
                                <div class="px-3 py-1 rounded-full bg-teal/10 text-teal text-[10px] font-bold">
                                    Bandingkan
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Kriteria B</span>
                                    <span class="text-sm font-bold text-slate-800 text-right">{{ $b->nama }} ({{ $b->kode }})</span>
                                </div>
                            </div>

                            <!-- Saaty scale buttons row -->
                            <div class="w-full flex items-center justify-between gap-1 bg-white p-2 rounded-xl border border-slate-100 shadow-inner overflow-x-auto">
                                <!-- LEFT SIDE: A is more important -->
                                <div class="flex items-center gap-1">
                                    @foreach([9, 8, 7, 6, 5, 4, 3, 2] as $val)
                                        <label class="flex flex-col items-center">
                                            <input type="radio" name="nilai[{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
                                                   x-model="activeVal" class="sr-only">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg cursor-pointer text-xs font-bold transition-all duration-150
                                                x-cloak"
                                                  :class="activeVal == '{{ $val }}' ? 'bg-teal text-white shadow-md shadow-teal/20 scale-105' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-700'">
                                                {{ $val }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <!-- MIDDLE: Equal -->
                                <label class="flex flex-col items-center">
                                    <input type="radio" name="nilai[{{ $a->id }}][{{ $b->id }}]" value="1" 
                                           x-model="activeVal" class="sr-only">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg cursor-pointer text-xs font-bold border border-slate-200 transition-all duration-150"
                                          :class="activeVal == '1' ? 'bg-teal text-white border-teal shadow-md shadow-teal/20 scale-105' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'">
                                        1
                                    </span>
                                </label>

                                <!-- RIGHT SIDE: B is more important -->
                                <div class="flex items-center gap-1">
                                    @foreach([2 => 0.5, 3 => 0.333333, 4 => 0.25, 5 => 0.2, 6 => 0.166667, 7 => 0.142857, 8 => 0.125, 9 => 0.111111] as $label => $val)
                                        <label class="flex flex-col items-center">
                                            <input type="radio" name="nilai[{{ $a->id }}][{{ $b->id }}]" value="{{ $val }}" 
                                                   x-model="activeVal" class="sr-only">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg cursor-pointer text-xs font-bold transition-all duration-150"
                                                  :class="activeVal == '{{ $val }}' || (parseFloat(activeVal).toFixed(3) == '{{ round($val, 3) }}') ? 'bg-teal text-white shadow-md shadow-teal/20 scale-105' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-700'">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Legend helper -->
                            <div class="flex justify-between items-center text-[10px] text-slate-400 font-medium px-2">
                                <span>← A Lebih Penting</span>
                                <span class="text-center font-bold">Sama Penting</span>
                                <span>B Lebih Penting →</span>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit" class="px-6 py-3 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-lg shadow-teal/25 hover:shadow-teal/35 hover:-translate-y-0.5 transition-all">
                            Simpan & Lanjutkan <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info (1/3 width) -->
        <div class="w-full lg:w-96 space-y-6">
            <!-- Consistency Status Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">Status Konsistensi</h3>
                
                @if($cr !== null)
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl flex items-center gap-3 {{ $isConsistent ? 'bg-teal-50 text-teal-dark border border-teal-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $isConsistent ? 'bg-teal/20 text-teal' : 'bg-red-500/20 text-red-600' }}">
                                <i class="fas {{ $isConsistent ? 'fa-check-circle' : 'fa-triangle-exclamation' }} text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold">
                                    {{ $isConsistent ? 'Konsisten' : 'Tidak Konsisten' }}
                                </p>
                                <p class="text-[10px] opacity-80 mt-0.5">
                                    Nilai CR saat ini: {{ number_format($cr, 5) }}
                                </p>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Matriks perbandingan dinyatakan **Konsisten** jika rasio konsistensi (**Consistency Ratio / CR**) bernilai **&le; 0.10**. 
                            Jika CR > 0.10, silakan sesuaikan kembali perbandingan Anda untuk mendapatkan hasil yang logis.
                        </p>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 text-xs flex items-center gap-3">
                        <i class="fas fa-circle-info text-slate-400"></i>
                        <span>Isi seluruh perbandingan dan klik simpan untuk memeriksa rasio konsistensi kriteria.</span>
                    </div>
                @endif
            </div>

            <!-- Suggestions Box (shown when not consistent) -->
            @if(!empty($suggestions) && count($suggestions) > 0)
                <div class="bg-white p-6 rounded-2xl border border-amber-200/80 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/20 text-amber-600">
                            <i class="fas fa-lightbulb text-sm"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Saran Perbaikan</h3>
                    </div>
                    
                    <p class="text-xs text-slate-500 mb-4">
                        Berdasarkan analisis matematis, berikut pasangan yang paling berkontribusi pada inkonsistensi:
                    </p>

                    <div class="space-y-3">
                        @foreach($suggestions as $index => $suggestion)
                            <div class="p-3 rounded-lg border {{ $suggestion['priority'] === 'high' ? 'border-red-200 bg-red-50/50' : ($suggestion['priority'] === 'medium' ? 'border-amber-200 bg-amber-50/50' : 'border-slate-200 bg-slate-50/50') }}">
                                <!-- Priority Badge -->
                                <div class="flex items-start gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide
                                        {{ $suggestion['priority'] === 'high' ? 'bg-red-500 text-white' : ($suggestion['priority'] === 'medium' ? 'bg-amber-500 text-white' : 'bg-slate-400 text-white') }}">
                                        {{ $suggestion['priority'] === 'high' ? '🔴 Prioritas Tinggi' : ($suggestion['priority'] === 'medium' ? '🟡 Prioritas Sedang' : '🟢 Opsional') }}
                                    </span>
                                </div>

                                <!-- Pair Information -->
                                <div class="text-xs font-bold text-slate-800 mb-1">
                                    {{ $suggestion['pair']['name_i'] }} vs {{ $suggestion['pair']['name_j'] }}
                                </div>

                                <!-- Current vs Suggested -->
                                <div class="flex items-center gap-2 text-[10px] mb-2">
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-500">Saat ini:</span>
                                        <span class="font-bold text-red-600">{{ $suggestion['current'] }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right text-slate-300"></i>
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-500">Disarankan:</span>
                                        <span class="font-bold text-teal">{{ $suggestion['suggested'] }}</span>
                                    </div>
                                </div>

                                <!-- Explanation -->
                                <p class="text-[10px] text-slate-600 leading-relaxed">
                                    {{ $suggestion['explanation'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info-circle text-blue-500 text-xs mt-0.5"></i>
                            <p class="text-[10px] text-blue-700 leading-relaxed">
                                <strong>Catatan:</strong> Nilai yang disarankan dihitung berdasarkan keseluruhan pola penilaian Anda. 
                                Sesuaikan nilai-nilai di atas, lalu klik <strong>Simpan & Lanjutkan</strong> untuk memeriksa ulang konsistensi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Saaty Scale Explanation -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">Panduan Skala Saaty</h3>
                <div class="space-y-3 text-xs text-slate-500">
                    <div class="flex gap-2">
                        <span class="font-bold text-teal min-w-[20px]">1</span>
                        <span>Kedua elemen sama pentingnya.</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-bold text-teal min-w-[20px]">3</span>
                        <span>Elemen yang satu sedikit lebih penting.</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-bold text-teal min-w-[20px]">5</span>
                        <span>Elemen yang satu lebih penting.</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-bold text-teal min-w-[20px]">7</span>
                        <span>Elemen yang satu sangat penting.</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="font-bold text-teal min-w-[20px]">9</span>
                        <span>Satu elemen mutlak penting dibanding lainnya.</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Nilai genap (2, 4, 6, 8) adalah nilai kompromi di antara dua pilihan ganjil yang berdekatan.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
