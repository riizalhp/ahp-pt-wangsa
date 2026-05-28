<x-layouts.app title="Penerimaan Barang Aktual">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk memproses dan mencatat kedatangan barang secara riil dari supplier.</p>
    </div>

    <!-- 1. Pending PO Queue (Needs input) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 bg-amber-50/40">
            <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-clock animate-pulse"></i> Antrean Kedatangan Barang (Menunggu Input Aktual)
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($pendingPos->isEmpty())
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-teal-50 text-teal flex items-center justify-center mb-2">
                        <i class="fas fa-check-double text-sm"></i>
                    </div>
                    <p class="text-xs text-slate-500 font-bold">Semua kiriman barang sudah tercatat aktual. Antrean kosong!</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3">No. PO</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Jumlah PO</th>
                            <th class="pb-3">Tanggal PO</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingPos as $po)
                            <tr>
                                <td class="py-3 font-semibold text-slate-500">#{{ $po->id }}</td>
                                <td class="py-3 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                <td class="py-3 font-medium text-slate-700">{{ $po->produk->nama }}</td>
                                <td class="py-3 text-slate-600 font-medium">{{ number_format($po->jumlah_dibeli) }} {{ $po->produk->satuan }}</td>
                                <td class="py-3 text-slate-500">{{ $po->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('logistik.aktual.edit', $po->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-sm transition-all">
                                        <i class="fas fa-clipboard-check"></i> Proses Aktual
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- 2. Completed PO List (Processed) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Riwayat Penerimaan Selesai Diproses
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($completedPos->isEmpty())
                <p class="text-xs text-slate-400 italic text-center py-6">Belum ada penerimaan aktual yang tercatat.</p>
            @else
                <table class="min-w-full divide-y divide-slate-150 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3">No. PO</th>
                            <th class="pb-3">Supplier</th>
                            <th class="pb-3">Produk</th>
                            <th class="pb-3">Tanggal Kedatangan</th>
                            <th class="pb-3 text-center">Diterima</th>
                            <th class="pb-3 text-center">Cacat</th>
                            <th class="pb-3 text-center">Persen Kualitas</th>
                            <th class="pb-3 text-center">Hari Terlambat</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($completedPos as $po)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 font-semibold text-slate-500">#{{ $po->id }}</td>
                                <td class="py-3 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                <td class="py-3 font-medium text-slate-700">{{ $po->produk->nama }}</td>
                                <td class="py-3 text-slate-500">{{ $po->tanggal_kedatangan->isoFormat('D MMMM Y') }}</td>
                                <td class="py-3 text-center font-semibold text-slate-700">{{ number_format($po->jumlah_diterima) }}</td>
                                <td class="py-3 text-center font-semibold text-red-600">{{ number_format($po->jumlah_cacat) }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-teal-50 text-teal-dark border border-teal-100">
                                        {{ number_format($po->persen_kualitas, 1) }}%
                                    </span>
                                </td>
                                <td class="py-3 text-center font-bold">
                                    @if($po->hari_keterlambatan > 0)
                                        <span class="text-red-600">+{{ $po->hari_keterlambatan }} hari</span>
                                    @elseif($po->hari_keterlambatan < 0)
                                        <span class="text-teal font-semibold">{{ $po->hari_keterlambatan }} hari</span>
                                    @else
                                        <span class="text-slate-500">Tepat waktu</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('logistik.aktual.edit', $po->id) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-150 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition-colors" title="Ubah data aktual">
                                        <i class="fas fa-edit text-xs"></i>
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
