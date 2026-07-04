<x-layouts.app title="Dashboard Administrator Purchasing">
    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Total PO Card -->
        <div class="bg-white p-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-file-invoice-dollar text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Purchase Order</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $poCount }}</dd>
            </div>
        </div>

        <!-- Pending PO Card -->
        <div class="bg-white p-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <i class="fas fa-clock text-lg animate-pulse"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Menunggu Penerimaan</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $pendingCount }}</dd>
            </div>
        </div>

        <!-- Received PO Card -->
        <div class="bg-white p-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal">
                <i class="fas fa-circle-check text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Diterima Aktual</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $receivedCount }}</dd>
            </div>
        </div>
    </div>

    <!-- Latest Purchase Orders Panel -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">5 Purchase Order Terakhir</h3>
            <a href="{{ route('sales.purchase_order.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-sm transition-colors">
                <i class="fas fa-plus"></i> PO Baru
            </a>
        </div>
        <div class="p-6">
            @if($latestPos->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-file-circle-exclamation"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada data Purchase Order.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="pb-3">No. PO</th>
                                <th class="pb-3">Supplier</th>
                                <th class="pb-3">Jumlah Item</th>
                                <th class="pb-3">Tanggal PO</th>
                                <th class="pb-3">Target Kedatangan</th>
                                <th class="pb-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($latestPos as $po)
                                @php
                                    $allReceived = $po->detail->every(fn($d) => !is_null($d->jumlah_diterima_baik));
                                    $someReceived = $po->detail->contains(fn($d) => !is_null($d->jumlah_diterima_baik));
                                @endphp
                                <tr>
                                    <td class="py-3 font-semibold text-slate-600">{{ $po->no_po }}</td>
                                    <td class="py-3 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                    <td class="py-3 text-slate-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">
                                            {{ $po->detail->count() }} item
                                        </span>
                                    </td>
                                    <td class="py-3 text-slate-500">{{ $po->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                    <td class="py-3 text-slate-500">
                                        @if($po->tanggal_kedatangan_target)
                                            {{ $po->tanggal_kedatangan_target->isoFormat('D MMM Y') }}
                                        @else
                                            <span class="italic text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        @if($allReceived)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-teal-50 text-teal-dark border border-teal-100">
                                                Diterima Semua
                                            </span>
                                        @elseif($someReceived)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                Diterima Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
