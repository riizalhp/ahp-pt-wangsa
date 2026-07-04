<x-layouts.app title="Daftar Purchase Order">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Daftar Purchase Order yang telah dibuat ke supplier mitra.</p>
        <a href="{{ route('sales.purchase_order.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
            <i class="fas fa-plus"></i> Buat PO Baru
        </a>
    </div>

    <!-- PO List Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 overflow-x-auto">
            @if($headers->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                        <i class="fas fa-file-circle-minus text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium mb-1">Belum ada data Purchase Order.</p>
                    <p class="text-xs text-slate-400">Klik "Buat PO Baru" untuk membuat Purchase Order pertama.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4 pr-4">No. PO</th>
                            <th class="pb-4 pr-4">Supplier</th>
                            <th class="pb-4 pr-4">Tanggal PO</th>
                            <th class="pb-4 pr-4">Tanggal Kedatangan Target</th>
                            <th class="pb-4 pr-4 text-center">Jumlah Item</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($headers as $header)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 pr-4 font-semibold text-teal font-mono text-xs">
                                    {{ $header->no_po }}
                                </td>
                                <td class="py-4 pr-4 font-bold text-slate-800">
                                    {{ $header->supplier->nama }}
                                </td>
                                <td class="py-4 pr-4 text-slate-500">
                                    {{ $header->tanggal_po->format('d/m/Y') }}
                                </td>
                                <td class="py-4 pr-4 text-slate-500">
                                    {{ $header->tanggal_kedatangan_target->format('d/m/Y') }}
                                </td>
                                <td class="py-4 pr-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-dark border border-teal-100">
                                        {{ $header->detail->count() }} item
                                    </span>
                                </td>
                                <td class="py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('sales.purchase_order.show', $header->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-teal hover:text-white transition-colors duration-150 text-xs font-bold">
                                            <i class="fas fa-eye text-[11px]"></i> Detail
                                        </a>
                                        
                                        <form action="{{ route('sales.purchase_order.destroy', $header->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus Purchase Order ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors duration-150 text-xs font-bold">
                                                <i class="fas fa-trash text-[11px]"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
