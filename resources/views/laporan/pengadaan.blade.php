<x-layouts.app title="Laporan Riwayat Pengadaan (PO)">
    <!-- Date Filter Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <form action="{{ url()->current() }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-1">
                <label for="from" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dari Tanggal PO</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}"
                       class="block w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-xs text-slate-800 font-medium">
            </div>

            <div class="flex-1">
                <label for="to" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal PO</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}"
                       class="block w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-xs text-slate-800 font-medium">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if(request()->filled('from') || request()->filled('to'))
                    <a href="{{ url()->current() }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-all flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- PO Report Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Daftar Purchase Order Pengadaan</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal/10 text-teal-dark border border-teal-100">
                {{ $pengadaans->count() }} Data PO
            </span>
        </div>
        
        <div class="p-6 overflow-x-auto">
            @if($pengadaans->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Tidak ada data Purchase Order ditemukan untuk periode filter terpilih.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-150 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3">No. PO</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Jumlah PO</th>
                            <th class="pb-3">Tanggal PO</th>
                            <th class="pb-3">Tanggal Datang</th>
                            <th class="pb-3 text-center">Kualitas</th>
                            <th class="pb-3 text-center">Keterlambatan</th>
                            <th class="pb-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($pengadaans as $po)
                            <tr class="hover:bg-slate-50/40">
                                <td class="py-3 font-semibold text-slate-500">#{{ $po->id }}</td>
                                <td class="py-3 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                <td class="py-3 font-medium text-slate-700">{{ $po->produk->nama }}</td>
                                <td class="py-3 text-slate-600">{{ number_format($po->jumlah_dibeli) }} {{ $po->produk->satuan }}</td>
                                <td class="py-3 text-slate-500">{{ $po->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                <td class="py-3 text-slate-550">
                                    {{ $po->tanggal_kedatangan ? $po->tanggal_kedatangan->isoFormat('D MMMM Y') : '-' }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($po->persen_kualitas !== null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-teal-50 text-teal-dark border border-teal-100">
                                            {{ number_format($po->persen_kualitas, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center font-bold">
                                    @if($po->hari_keterlambatan !== null)
                                        @if($po->hari_keterlambatan > 0)
                                            <span class="text-red-600">+{{ $po->hari_keterlambatan }} hari</span>
                                        @elseif($po->hari_keterlambatan < 0)
                                            <span class="text-teal">{{ $po->hari_keterlambatan }} hari</span>
                                        @else
                                            <span class="text-slate-500 text-xs">Tepat waktu</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-slate-400 text-xs truncate max-w-xs" title="{{ $po->catatan }}">
                                    {{ $po->catatan ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
