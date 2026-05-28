<x-layouts.app title="Kelola Data Supplier">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk menambah, merubah, dan menghapus data rekanan supplier.</p>
        <a href="{{ route('supervisor.supplier.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
            <i class="fas fa-plus"></i> Tambah Supplier
        </a>
    </div>

    <!-- Suppliers Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 overflow-x-auto">
            @if($suppliers->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada data supplier. Klik "Tambah Supplier" untuk memulai.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4">Kode</th>
                            <th class="pb-4">Nama Supplier</th>
                            <th class="pb-4">Kontak</th>
                            <th class="pb-4">Alamat</th>
                            <th class="pb-4 text-center">Rekap Cacat</th>
                            <th class="pb-4 text-center">Rekap Terlambat</th>
                            <th class="pb-4 text-center">Rata-rata Terlambat</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($suppliers as $supplier)
                            <tr>
                                <td class="py-4 font-semibold text-slate-500">{{ $supplier->kode }}</td>
                                <td class="py-4 font-bold text-slate-800">{{ $supplier->nama }}</td>
                                <td class="py-4 text-slate-600 space-y-0.5">
                                    @if($supplier->telepon)
                                        <div class="flex items-center gap-1 text-xs"><i class="fas fa-phone text-slate-400 text-[10px]"></i> {{ $supplier->telepon }}</div>
                                    @endif
                                    @if($supplier->email)
                                        <div class="flex items-center gap-1 text-xs"><i class="fas fa-envelope text-slate-400 text-[10px]"></i> {{ $supplier->email }}</div>
                                    @endif
                                </td>
                                <td class="py-4 text-slate-500 max-w-xs truncate text-xs">{{ $supplier->alamat ?? '-' }}</td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                        {{ number_format($supplier->total_persen_cacat, 1) }}%
                                    </span>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                        {{ number_format($supplier->total_persen_keterlambatan, 1) }}%
                                    </span>
                                </td>
                                <td class="py-4 text-center text-slate-700 font-semibold">
                                    {{ number_format($supplier->mean_hari_keterlambatan, 1) }} hari
                                </td>
                                <td class="py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('supervisor.supplier.edit', $supplier->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-teal hover:text-white transition-colors duration-150" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('supervisor.supplier.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-red-500 hover:bg-red-600 hover:text-white transition-colors duration-150" title="Hapus">
                                                <i class="fas fa-trash text-xs"></i>
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
