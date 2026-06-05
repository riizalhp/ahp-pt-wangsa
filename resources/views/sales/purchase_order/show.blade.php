<x-layouts.app title="Detail Purchase Order">
    <div class="mb-6">
        <a href="{{ route('sales.purchase_order.index') }}"
           class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1 w-fit">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- ── PO Header Details ── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-file-invoice text-teal"></i>
                Informasi Purchase Order
            </h2>
            <span class="text-xs font-mono font-bold text-teal bg-teal-50 border border-teal-100 px-3 py-1 rounded-lg">
                {{ $purchase_order->no_po }}
            </span>
        </div>

        <div class="p-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Supplier --}}
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Supplier</dt>
                <dd class="text-sm font-bold text-slate-800">{{ $purchase_order->supplier->nama }}</dd>
                <dd class="text-xs text-slate-500 mt-0.5">{{ $purchase_order->supplier->kode }}</dd>
            </div>

            {{-- Tanggal PO --}}
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal PO</dt>
                <dd class="text-sm font-semibold text-slate-700">
                    {{ $purchase_order->tanggal_po->format('d/m/Y') }}
                </dd>
            </div>

            {{-- Tanggal Kedatangan Target --}}
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Target Kedatangan</dt>
                <dd class="text-sm font-semibold text-slate-700">
                    {{ $purchase_order->tanggal_kedatangan_target->format('d/m/Y') }}
                </dd>
            </div>

            {{-- Catatan --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan</dt>
                <dd class="text-sm text-slate-600">
                    @if($purchase_order->catatan)
                        {{ $purchase_order->catatan }}
                    @else
                        <span class="italic text-slate-400">Tidak ada catatan.</span>
                    @endif
                </dd>
            </div>

        </div>
    </div>

    {{-- ── Detail Line Items ── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-list-ul text-teal"></i>
                Detail Produk
                <span class="ml-1 text-xs font-bold bg-teal-50 text-teal-dark border border-teal-100 px-2 py-0.5 rounded-full">
                    {{ $purchase_order->detail->count() }} item
                </span>
            </h2>
        </div>

        <div class="p-6 overflow-x-auto">
            @if($purchase_order->detail->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada item produk pada Purchase Order ini.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4 pr-4 w-8">No.</th>
                            <th class="pb-4 pr-4">Produk</th>
                            <th class="pb-4 pr-4">Jenis Produk</th>
                            <th class="pb-4 pr-4 text-right">Jumlah Dipesan</th>
                            <th class="pb-4 pr-4">Satuan</th>
                            <th class="pb-4 pr-4 text-right">Jumlah Diterima Baik</th>
                            <th class="pb-4 pr-4 text-right">% Kualitas</th>
                            <th class="pb-4 text-right">Hari Terlambat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($purchase_order->detail as $i => $detail)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 pr-4 text-xs text-slate-400 font-medium">
                                    {{ $i + 1 }}.
                                </td>
                                <td class="py-4 pr-4 font-bold text-slate-800">
                                    {{ $detail->produk->nama }}
                                </td>
                                <td class="py-4 pr-4 text-slate-500">
                                    @if($detail->produk->jenis_produk)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                            {{ $detail->produk->jenis_produk }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-right font-semibold text-slate-700">
                                    {{ number_format($detail->jumlah_dipesan, 2) }}
                                </td>
                                <td class="py-4 pr-4 text-slate-600">
                                    {{ $detail->satuan }}
                                </td>
                                <td class="py-4 pr-4 text-right">
                                    @if(!is_null($detail->jumlah_diterima_baik))
                                        <span class="font-semibold text-slate-700">
                                            {{ number_format($detail->jumlah_diterima_baik, 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 italic text-xs">Belum diterima</span>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-right">
                                    @if(!is_null($detail->persen_kualitas_item))
                                        @php $pct = round($detail->persen_kualitas_item, 1); @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                            {{ $pct >= 80 ? 'bg-teal-50 text-teal-dark border border-teal-100' : ($pct >= 50 ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-red-50 text-red-600 border border-red-100') }}">
                                            {{ $pct }}%
                                        </span>
                                    @else
                                        <span class="text-slate-300 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="py-4 text-right">
                                    @if(!is_null($detail->hari_keterlambatan))
                                        @if($detail->hari_keterlambatan > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                                <i class="fas fa-clock text-[10px]"></i>
                                                {{ $detail->hari_keterlambatan }} hari
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-teal-50 text-teal-dark border border-teal-100">
                                                <i class="fas fa-check text-[10px]"></i>
                                                Tepat waktu
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-300 italic text-xs">—</span>
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
