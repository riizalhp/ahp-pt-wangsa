<x-layouts.app title="Dashboard Utama">
    <!-- Quick Overview Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Supplier Count Card -->
        <div class="relative overflow-hidden bg-white px-4 py-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal">
                <i class="fas fa-truck text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Supplier</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $supplierCount }}</dd>
            </div>
        </div>

        <!-- Product Count Card -->
        <div class="relative overflow-hidden bg-white px-4 py-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Produk</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $produkCount }}</dd>
            </div>
        </div>

        <!-- Kriteria Count Card -->
        <div class="relative overflow-hidden bg-white px-4 py-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <i class="fas fa-list-ol text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kriteria</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $kriteriaCount }}</dd>
            </div>
        </div>

        <!-- Subkriteria Count Card -->
        <div class="relative overflow-hidden bg-white px-4 py-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Subkriteria</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $subkriteriaCount }}</dd>
            </div>
        </div>
    </div>

    <!-- Main Section Grid -->
    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-3">
        <!-- AHP Rankings Panel -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide">Top 5 Supplier Terpilih (AHP)</h3>
                <a href="{{ route('supervisor.laporan.penilaian') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
                    Lihat Semua <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
            <div class="p-6">
                @if($rankings->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Belum ada perhitungan AHP yang disimpan.</p>
                        <a href="{{ route('supervisor.ahp.kriteria') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold shadow-md hover:bg-teal-dark transition-colors duration-150">
                            <i class="fas fa-calculator"></i> Mulai Perhitungan
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="pb-3">Peringkat</th>
                                    <th class="pb-3">Kode</th>
                                    <th class="pb-3">Nama Supplier</th>
                                    <th class="pb-3 text-right">Nilai Akhir AHP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($rankings as $rank)
                                    <tr>
                                        <td class="py-3">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold text-xs
                                                {{ $rank->ranking == 1 ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                                {{ $rank->ranking == 2 ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $rank->ranking == 3 ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $rank->ranking > 3 ? 'text-slate-400' : '' }}">
                                                {{ $rank->ranking }}
                                            </span>
                                        </td>
                                        <td class="py-3 font-semibold text-slate-500">{{ $rank->supplier->kode }}</td>
                                        <td class="py-3 font-bold text-slate-800">{{ $rank->supplier->nama }}</td>
                                        <td class="py-3 text-right font-bold text-teal">{{ number_format($rank->nilai_akhir, 4) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide">Pintasan Cepat</h3>
            </div>
            <div class="p-6 flex-1 space-y-3">
                <a href="{{ route('supervisor.supplier.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition-all duration-150 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal/10 text-teal group-hover:scale-105 transition-transform">
                        <i class="fas fa-truck-plus"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Tambah Supplier</p>
                        <p class="text-[10px] text-slate-400">Daftarkan vendor mitra baru</p>
                    </div>
                </a>
                
                <a href="{{ route('supervisor.produk.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition-all duration-150 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:scale-105 transition-transform">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Tambah Produk</p>
                        <p class="text-[10px] text-slate-400">Daftarkan jenis inventori baru</p>
                    </div>
                </a>

                <a href="{{ route('supervisor.ahp.kriteria') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50/50 border border-teal-100/50 hover:border-teal-200 transition-all duration-150 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal text-white group-hover:scale-105 transition-transform shadow-md shadow-teal/20">
                        <i class="fas fa-calculator text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-teal-dark">Mulai Hitung AHP</p>
                        <p class="text-[10px] text-teal-500">Bandingkan berpasangan & ranking</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
