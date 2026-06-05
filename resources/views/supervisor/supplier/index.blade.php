<x-layouts.app title="Kelola Data Supplier">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk menambah, merubah, dan menghapus data rekanan supplier.</p>
        <a href="{{ route('supervisor.supplier.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
            <i class="fas fa-plus"></i> Tambah Supplier
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('supervisor.supplier.index') }}" class="mb-5">
        <div class="flex items-center gap-2 max-w-md">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800"
                   placeholder="Cari nama supplier...">
            <button type="submit" class="shrink-0 px-4 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark transition-colors duration-150">
                Cari
            </button>
            @if(!empty($search))
                <a href="{{ route('supervisor.supplier.index') }}" class="shrink-0 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-colors duration-150">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Suppliers Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 overflow-x-auto">
            @if($suppliers->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">
                        @if(!empty($search))
                            Tidak ada supplier dengan nama "{{ $search }}".
                        @else
                            Belum ada data supplier. Klik "Tambah Supplier" untuk memulai.
                        @endif
                    </p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4">Kode</th>
                            <th class="pb-4">Nama Supplier</th>
                            <th class="pb-4">Jenis Barang</th>
                            <th class="pb-4">Kontak Person</th>
                            <th class="pb-4">No Telp</th>
                            <th class="pb-4">Alamat</th>
                            <th class="pb-4 text-center">Lama Kerja Sama</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($suppliers as $supplier)
                            <tr>
                                <td class="py-4 font-semibold text-slate-500">{{ $supplier->kode }}</td>
                                <td class="py-4 font-bold text-slate-800">{{ $supplier->nama }}</td>
                                <td class="py-4 text-slate-600 text-xs">{{ $supplier->jenis_barang ?? '-' }}</td>
                                <td class="py-4 text-slate-600 text-xs">{{ $supplier->kontak_person ?? '-' }}</td>
                                <td class="py-4 text-slate-600 text-xs">{{ $supplier->telepon ?? '-' }}</td>
                                <td class="py-4 text-slate-500 max-w-xs truncate text-xs">{{ $supplier->alamat ?? '-' }}</td>
                                <td class="py-4 text-center text-slate-600 text-xs">
                                    @if($supplier->lama_kerja_sama)
                                        {{ $supplier->lama_kerja_sama }} tahun
                                    @else
                                        -
                                    @endif
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
