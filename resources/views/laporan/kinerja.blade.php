<x-layouts.app title="Kinerja Supplier">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-slate-500 font-medium">Halaman untuk memantau metrik kinerja supplier berdasarkan data penerimaan barang yang telah diinputkan.</p>
            <div class="mt-2 p-3 rounded-lg bg-blue-50 border border-blue-100">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <p class="text-xs text-blue-800 leading-relaxed">
                        <span class="font-bold">Catatan Perhitungan:</span> Persentase Keterlambatan, Persentase Cacat, dan Rata-rata Hari Keterlambatan dihitung secara kumulatif dari seluruh item pada detail Purchase Order (PO) masing-masing supplier.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kinerja Supplier Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Data Kinerja Supplier</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal/10 text-teal-dark border border-teal-100">
                {{ $suppliers->count() }} Supplier
            </span>
        </div>

        <div class="p-6 overflow-x-auto">
            @if($suppliers->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-truck"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">
                        Belum ada supplier dengan data kinerja. Data akan muncul setelah logistik menginputkan penerimaan barang.
                    </p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-150 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-3 pr-4">Nama Supplier</th>
                            <th class="pb-3 pr-4">Jenis Barang</th>
                            <th class="pb-3 pr-4">Alamat</th>
                            <th class="pb-3 pr-4 text-center">% Keterlambatan</th>
                            <th class="pb-3 pr-4 text-center">% Cacat</th>
                            <th class="pb-3 text-center">Mean Hari Keterlambatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($suppliers as $supplier)
                            <tr class="hover:bg-slate-50/40">
                                <td class="py-3 pr-4 font-bold text-slate-800">{{ $supplier->nama }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $supplier->jenis_barang ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-500 text-xs max-w-xs truncate" title="{{ $supplier->alamat }}">
                                    {{ $supplier->alamat ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    @php $keterlambatan = $supplier->total_persen_keterlambatan ?? 0; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border
                                        {{ $keterlambatan > 0 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-teal/10 text-teal-dark border-teal-100' }}">
                                        {{ number_format($keterlambatan, 1) }}%
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    @php $cacat = $supplier->total_persen_cacat ?? 0; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border
                                        {{ $cacat > 0 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-teal/10 text-teal-dark border-teal-100' }}">
                                        {{ number_format($cacat, 1) }}%
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="text-sm font-bold text-slate-700">
                                        {{ number_format($supplier->mean_hari_keterlambatan ?? 0, 1) }} hari
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
