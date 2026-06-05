<x-layouts.app title="Penerimaan Barang">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk mencatat penerimaan barang dari Purchase Order yang masuk.</p>
    </div>

    <!-- 1. PO Belum Selesai (Pending) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 bg-amber-50/40">
            <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-clock animate-pulse"></i> PO Belum Selesai
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($pendingHeaders->isEmpty())
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-teal-50 text-teal flex items-center justify-center mb-2">
                        <i class="fas fa-check-double text-sm"></i>
                    </div>
                    <p class="text-xs text-slate-500 font-bold">Semua Purchase Order sudah diterima. Tidak ada yang menunggu!</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3">No. PO</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3">Tanggal PO</th>
                            <th class="pb-3">Target Kedatangan</th>
                            <th class="pb-3 text-center">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingHeaders as $header)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 font-semibold text-slate-700">{{ $header->no_po }}</td>
                                <td class="py-3 font-bold text-slate-800">{{ $header->supplier->nama }}</td>
                                <td class="py-3 text-slate-500">{{ $header->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                <td class="py-3 text-slate-500">
                                    @if($header->tanggal_kedatangan_target)
                                        {{ $header->tanggal_kedatangan_target->isoFormat('D MMMM Y') }}
                                    @else
                                        <span class="italic text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        <i class="fas fa-hourglass-half mr-1 text-[10px]"></i> Belum Diterima
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('logistik.penerimaan.edit', $header->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-sm transition-all">
                                        <i class="fas fa-clipboard-check"></i> Input Penerimaan
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- 2. PO Selesai (Completed) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-check-circle text-teal"></i> PO Selesai
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($completedHeaders->isEmpty())
                <p class="text-xs text-slate-400 italic text-center py-6">Belum ada Purchase Order yang selesai diterima.</p>
            @else
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3">No. PO</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3">Tanggal PO</th>
                            <th class="pb-3">Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($completedHeaders as $header)
                            @php
                                {{-- Use the latest actual arrival date among all detail items as the completion date --}}
                                $tanggalSelesai = $header->detail
                                    ->filter(fn($d) => !is_null($d->tanggal_kedatangan_aktual))
                                    ->max('tanggal_kedatangan_aktual');
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 font-semibold text-slate-700">{{ $header->no_po }}</td>
                                <td class="py-3 font-bold text-slate-800">{{ $header->supplier->nama }}</td>
                                <td class="py-3 text-slate-500">{{ $header->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                <td class="py-3 text-slate-500">
                                    @if($tanggalSelesai)
                                        {{ \Carbon\Carbon::parse($tanggalSelesai)->isoFormat('D MMMM Y') }}
                                    @else
                                        <span class="italic text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
