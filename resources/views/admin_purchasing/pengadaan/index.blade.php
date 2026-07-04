<x-layouts.app title="Daftar Purchase Order (Pengadaan)">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk mencatat pemesanan produk (Purchase Order) baru ke supplier mitra.</p>
        <a href="{{ route('admin_purchasing.pengadaan.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
            <i class="fas fa-plus"></i> Buat PO Baru
        </a>
    </div>

    <!-- PO List Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 overflow-x-auto">
            @if($pengadaans->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <i class="fas fa-file-circle-minus"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada data Purchase Order. Klik "Buat PO Baru" untuk memulai.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="pb-4">No. PO</th>
                            <th class="pb-4">Supplier</th>
                            <th class="pb-4">Produk</th>
                            <th class="pb-4">Jumlah Dipesan</th>
                            <th class="pb-4">Tanggal PO</th>
                            <th class="pb-4">Dokumentasi</th>
                            <th class="pb-4 text-center">Status</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($pengadaans as $po)
                            <tr>
                                <td class="py-4 font-semibold text-slate-500">#{{ $po->id }}</td>
                                <td class="py-4 font-bold text-slate-800">{{ $po->supplier->nama }}</td>
                                <td class="py-4 font-medium text-slate-700">{{ $po->produk->nama }}</td>
                                <td class="py-4 text-slate-600 font-medium">{{ number_format($po->jumlah_dibeli) }} {{ $po->produk->satuan }}</td>
                                <td class="py-4 text-slate-500">{{ $po->tanggal_po->isoFormat('D MMMM Y') }}</td>
                                <td class="py-4">
                                    @if($po->foto_path)
                                        <a href="{{ asset($po->foto_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-teal hover:underline font-bold">
                                            <i class="fas fa-image"></i> Lihat Foto
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tidak ada foto</span>
                                    @endif
                                </td>
                                <td class="py-4 text-center">
                                    @if($po->tanggal_kedatangan)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-teal-50 text-teal-dark border border-teal-100">
                                            <i class="fas fa-check-circle mr-1 text-[10px]"></i> Diterima Aktual
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                            <i class="fas fa-clock mr-1 text-[10px]"></i> Sedang Dikirim
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($po->tanggal_kedatangan === null)
                                            <a href="{{ route('admin_purchasing.pengadaan.edit', $po->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-teal hover:text-white transition-colors duration-150" title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <form action="{{ route('admin_purchasing.pengadaan.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PO ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-red-500 hover:bg-red-600 hover:text-white transition-colors duration-150" title="Hapus">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Sudah terkunci</span>
                                        @endif
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
