<x-layouts.app title="Tambah Supplier Baru">
    <div class="mb-6">
        <a href="{{ route('supervisor.supplier.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('supervisor.supplier.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="kode" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kode Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" id="kode" required value="{{ old('kode') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: SUP001">
                    @error('kode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nama" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: PT ABC Indonesia">
                    @error('nama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="jenis_barang" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="jenis_barang" id="jenis_barang" required value="{{ old('jenis_barang') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Alat Tulis Kantor">
                    @error('jenis_barang') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="kontak_person" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kontak Person</label>
                    <input type="text" name="kontak_person" id="kontak_person" value="{{ old('kontak_person') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Budi Santoso">
                    @error('kontak_person') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="telepon" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">No Telp</label>
                    <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: 08123456789">
                    @error('telepon') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="lama_kerja_sama" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lama Kerja Sama (Tahun)</label>
                    <input type="number" name="lama_kerja_sama" id="lama_kerja_sama" min="0" value="{{ old('lama_kerja_sama') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: 3">
                    @error('lama_kerja_sama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="alamat" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alamat Lengkap</label>
                <textarea name="alamat" id="alamat" rows="3"
                          class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                          placeholder="Masukkan alamat fisik supplier">{{ old('alamat') }}</textarea>
                @error('alamat') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('supervisor.supplier.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                    <i class="fas fa-save mr-1"></i> Simpan Supplier
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
