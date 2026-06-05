<x-layouts.app title="Laporan Riwayat Pengadaan (PO)">
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
                    <p class="text-sm text-slate-500 font-medium">Belum ada Purchase Order yang tercatat.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-150 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3 pr-4">No. PO</th>
                            <th class="pb-3 pr-4">Supplier</th>
                            <th class="pb-3 pr-4">Tanggal PO</th>
                            <th class="pb-3 pr-4">Target Kedatangan</th>
                            <th class="pb-3 pr-4 text-center">Jumlah Item</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($pengadaans as $po)
                            <tr class="hover:bg-slate-50/40">
                                <td class="py-3 pr-4 font-semibold text-teal-dark">{{ $po->no_po }}</td>
                                <td class="py-3 pr-4 font-bold text-slate-800">{{ $po->supplier->nama ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-500">
                                    {{ $po->tanggal_po ? $po->tanggal_po->isoFormat('D MMMM Y') : '-' }}
                                </td>
                                <td class="py-3 pr-4 text-slate-500">
                                    {{ $po->tanggal_kedatangan_target ? $po->tanggal_kedatangan_target->isoFormat('D MMMM Y') : '-' }}
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">
                                        {{ $po->detail->count() }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('supervisor.laporan.riwayat.detail', $po->id) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-teal hover:text-teal-dark">
                                        Detail <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
