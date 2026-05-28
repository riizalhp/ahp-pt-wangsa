<x-layouts.app title="Buat Purchase Order Baru">
    <div class="mb-6">
        <a href="{{ route('sales.pengadaan.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('sales.pengadaan.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="supplier_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" id="supplier_id" required
                            class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                        <option value="">Pilih Supplier...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                [{{ $s->kode }}] {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="produk_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Produk <span class="text-red-500">*</span></label>
                    <select name="produk_id" id="produk_id" required
                            class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                        <option value="">Pilih Produk...</option>
                        @foreach($produks as $p)
                            <option value="{{ $p->id }}" {{ old('produk_id') == $p->id ? 'selected' : '' }}>
                                [{{ $p->kode }}] {{ $p->nama }} (Rp {{ number_format($p->harga, 0, ',', '.') }}/{{ $p->satuan }})
                            </option>
                        @endforeach
                    </select>
                    @error('produk_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="jumlah_dibeli" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Dipesan <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_dibeli" id="jumlah_dibeli" required value="{{ old('jumlah_dibeli') }}" min="1"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: 100">
                    @error('jumlah_dibeli') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="tanggal_po" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal PO <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_po" id="tanggal_po" required value="{{ old('tanggal_po', date('Y-m-d')) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                    @error('tanggal_po') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="foto" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Unggah Foto Dokumentasi/Laporan PO</label>
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal/10 file:text-teal hover:file:bg-teal/20 cursor-pointer">
                <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP. Maksimum ukuran: 4MB.</span>
                @error('foto') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan Tambahan</label>
                <textarea name="catatan" id="catatan" rows="3"
                          class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                          placeholder="Tulis instruksi khusus atau catatan pengadaan disini">{{ old('catatan') }}</textarea>
                @error('catatan') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('sales.pengadaan.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                    <i class="fas fa-save mr-1"></i> Simpan PO
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
