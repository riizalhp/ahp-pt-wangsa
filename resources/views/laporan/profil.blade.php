<x-layouts.app title="Kinerja Supplier">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk memantau detail profil dan statistik rekap kinerja pengadaan masing-masing supplier.</p>
    </div>

    <!-- Suppliers Grid Cards (Rich Aesthetic) -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @if($suppliers->isEmpty())
            <div class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center col-span-full">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                    <i class="fas fa-truck"></i>
                </div>
                <p class="text-sm text-slate-500 font-medium">Data supplier belum tersedia.</p>
            </div>
        @else
            @foreach($suppliers as $supplier)
                @php
                    $detailRoute = route(auth()->user()->role . '.laporan.profil.detail', $supplier->id);
                @endphp
                
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <!-- Header with Kode & Name -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal/10 text-teal-dark border border-teal-100/50 uppercase">
                                    {{ $supplier->kode }}
                                </span>
                                <h3 class="text-sm font-extrabold text-slate-800 mt-2 leading-snug">{{ $supplier->nama }}</h3>
                            </div>
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <i class="fas fa-building text-sm"></i>
                            </div>
                        </div>

                        <!-- Mini Stats Rekap Grid -->
                        <div class="grid grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100/80 text-center mb-4">
                            <div>
                                <span class="block text-[8px] text-slate-400 font-bold uppercase tracking-wider">Cacat</span>
                                <span class="block text-xs font-bold text-red-650 mt-0.5">{{ number_format($supplier->total_persen_cacat, 1) }}%</span>
                            </div>
                            <div class="border-x border-slate-200/50">
                                <span class="block text-[8px] text-slate-400 font-bold uppercase tracking-wider">Terlambat</span>
                                <span class="block text-xs font-bold text-amber-650 mt-0.5">{{ number_format($supplier->total_persen_keterlambatan, 1) }}%</span>
                            </div>
                            <div>
                                <span class="block text-[8px] text-slate-400 font-bold uppercase tracking-wider">Mean Delay</span>
                                <span class="block text-xs font-bold text-slate-800 mt-0.5">{{ number_format($supplier->mean_hari_keterlambatan, 1) }}h</span>
                            </div>
                        </div>

                        <!-- Core master info -->
                        <div class="space-y-1 text-xs text-slate-500 font-medium border-t border-slate-100 pt-3">
                            @if($supplier->telepon)
                                <div class="flex items-center gap-1.5"><i class="fas fa-phone text-slate-400 text-[10px] w-3 text-center"></i> {{ $supplier->telepon }}</div>
                            @endif
                            @if($supplier->email)
                                <div class="flex items-center gap-1.5"><i class="fas fa-envelope text-slate-400 text-[10px] w-3 text-center"></i> {{ $supplier->email }}</div>
                            @endif
                            <div class="flex items-start gap-1.5 mt-1.5">
                                <i class="fas fa-map-marker-alt text-slate-400 text-[10px] w-3 text-center mt-0.5"></i>
                                <p class="line-clamp-2 leading-relaxed text-[11px]">{{ $supplier->alamat ?? 'Tidak ada alamat lengkap.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Link -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                        <a href="{{ $detailRoute }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal hover:text-teal-dark">
                            Detail Kinerja <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-layouts.app>
