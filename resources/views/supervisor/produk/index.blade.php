<x-layouts.app title="Kelola Data Produk">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk menambah, merubah, dan menghapus data produk inventori yang dipesan.</p>
        <a href="{{ route('supervisor.produk.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    <!-- Products Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 overflow-x-auto">
            @if($produks->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada data produk. Klik "Tambah Produk" untuk memulai.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4">Kode Produk</th>
                            <th class="pb-4">Nama Produk</th>
                            <th class="pb-4">Satuan</th>
                            <th class="pb-4">Harga Satuan</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($produks as $produk)
                            <tr>
                                <td class="py-4 font-semibold text-slate-500">{{ $produk->kode }}</td>
                                <td class="py-4 font-bold text-slate-800">{{ $produk->nama }}</td>
                                <td class="py-4 text-slate-600 font-medium">{{ $produk->satuan }}</td>
                                <td class="py-4 text-slate-700 font-semibold">Rp {{ number_format($produk->harga, 2, ',', '.') }}</td>
                                <td class="py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('supervisor.produk.edit', $produk->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-teal hover:text-white transition-colors duration-150" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('supervisor.produk.destroy', $produk->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" class="inline">
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
