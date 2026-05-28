<x-layouts.app title="Dashboard Staff Logistik">
    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <!-- Pending Delivery Card -->
        <div class="bg-white p-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <i class="fas fa-truck-ramp-box text-lg animate-bounce"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Antrean Penerimaan Aktual</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $pendingCount }} PO</dd>
            </div>
        </div>

        <!-- Completed Delivery Card -->
        <div class="bg-white p-5 shadow-sm rounded-2xl border border-slate-200/80 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal">
                <i class="fas fa-clipboard-check text-lg"></i>
            </div>
            <div>
                <dt class="truncate text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Selesai Diproses</dt>
                <dd class="text-2xl font-bold text-slate-800 tracking-tight">{{ $receivedCount }} PO</dd>
            </div>
        </div>
    </div>

    <!-- Active Delivery Pipeline / Task List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Daftar Antrean Penerimaan (Belum Masuk Aktual)</h3>
            <a href="{{ route('logistik.aktual.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
                Proses Semua <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </div>
        <div class="p-6">
            @if($pendingDeliveries->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-check-circle text-teal"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Bagus! Semua Purchase Order sudah selesai diproses.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="pb-3">No. PO</th>
                                <th class="pb-3">Supplier</th>
                                <th class="pb-3">Produk</th>
                                <th class="pb-3">Jumlah Dipesan</th>
                                <th class="pb-3">Tanggal PO</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($pendingDeliveries as $po)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-600">#{{ $po->id }}</td>
                                    <td class="py-3 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                    <td class="py-3 font-medium text-slate-700">{{ $po->produk->nama }}</td>
                                    <td class="py-3 text-slate-600">{{ number_format($po->jumlah_dibeli) }} {{ $po->produk->satuan }}</td>
                                    <td class="py-3 text-slate-500">{{ $po->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('logistik.aktual.edit', $po->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-sm transition-colors">
                                            <i class="fas fa-clipboard-list"></i> Terima Aktual
                                        </a>
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
