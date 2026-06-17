<x-layouts.app title="Detail Purchase Order - {{ $header->no_po }}">
    <!-- Back link -->
    <div class="mb-5">
        <a href="{{ route('supervisor.laporan.pengadaan') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-teal-dark">
            <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Riwayat Pengadaan
        </a>
    </div>

    <!-- Header Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Informasi Purchase Order</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal/10 text-teal-dark border border-teal-100 uppercase">
                {{ $header->no_po }}
            </span>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. PO</span>
                <span class="text-sm font-bold text-teal-dark">{{ $header->no_po }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Supplier</span>
                <span class="text-sm font-semibold text-slate-800">{{ $header->supplier->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal PO</span>
                <span class="text-sm text-slate-700">
                    {{ $header->tanggal_po ? $header->tanggal_po->isoFormat('D MMMM Y') : '-' }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Target Kedatangan</span>
                <span class="text-sm text-slate-700">
                    {{ $header->tanggal_kedatangan_target ? $header->tanggal_kedatangan_target->isoFormat('D MMMM Y') : '-' }}
                </span>
            </div>
            <div class="sm:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan</span>
                <span class="text-sm text-slate-600">{{ $header->catatan ?: '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Detail Items Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Item Pesanan</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal/10 text-teal-dark border border-teal-100">
                {{ $header->detail->count() }} Item
            </span>
        </div>

        <div class="p-6 overflow-x-auto">
            @if($header->detail->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Tidak ada item pesanan untuk Purchase Order ini.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-150 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3 pr-4">No</th>
                            <th class="pb-3 pr-4">Produk</th>
                            <th class="pb-3 pr-4">Jenis Produk</th>
                            <th class="pb-3 pr-4">Merk</th>
                            <th class="pb-3 pr-4">Ukuran</th>
                            <th class="pb-3 pr-4 text-right">Jumlah Dipesan</th>
                            <th class="pb-3 pr-4">Satuan</th>
                            <th class="pb-3">Tanggal Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($header->detail as $index => $item)
                            <tr class="hover:bg-slate-50/40">
                                <td class="py-3 pr-4 text-slate-400 text-xs">{{ $index + 1 }}</td>
                                <td class="py-3 pr-4 font-bold text-slate-800">
                                    {{ $item->produk->nama ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 text-slate-600">
                                    {{ $item->produk->jenis_produk ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 text-slate-600">
                                    {{ $item->produk->merk ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 text-slate-500 text-xs">
                                    {{ $item->produk->ukuran ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 text-right font-semibold text-slate-700">
                                    {{ number_format((float) $item->jumlah_dipesan, 2) }}
                                </td>
                                <td class="py-3 pr-4 text-slate-500">
                                    {{ $item->satuan }}
                                </td>
                                <td class="py-3">
                                    @if($item->tanggal_kedatangan_aktual)
                                        <span class="text-xs text-slate-700">
                                            {{ $item->tanggal_kedatangan_aktual->isoFormat('D MMM Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                            Belum diterima
                                        </span>
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
