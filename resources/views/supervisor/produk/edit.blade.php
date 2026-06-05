<x-layouts.app title="Ubah Data Produk">
    <div class="mb-6">
        <a href="{{ route('supervisor.produk.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('supervisor.produk.update', $produk->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Supplier -->
                <div class="sm:col-span-2">
                    <label for="supplier_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" id="supplier_id" required
                            class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $produk->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Nama Produk -->
                <div class="sm:col-span-2">
                    <label for="nama" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama', $produk->nama) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Duplex Coat 310 gr">
                    @error('nama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Jenis Produk -->
                <div>
                    <label for="jenis_produk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Produk</label>
                    <input type="text" name="jenis_produk" id="jenis_produk" value="{{ old('jenis_produk', $produk->jenis_produk) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Duplex Coat 310 gr">
                    @error('jenis_produk') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Merk -->
                <div>
                    <label for="merk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Merk</label>
                    <input type="text" name="merk" id="merk" value="{{ old('merk', $produk->merk) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Hansol">
                    @error('merk') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Ukuran -->
                <div>
                    <label for="ukuran" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ukuran</label>
                    <input type="text" name="ukuran" id="ukuran" value="{{ old('ukuran', $produk->ukuran) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: 79x109">
                    @error('ukuran') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Kapasitas Pasokan -->
                <div>
                    <label for="kapasitas_pasokan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas Pasokan</label>
                    <input type="text" name="kapasitas_pasokan" id="kapasitas_pasokan" value="{{ old('kapasitas_pasokan', $produk->kapasitas_pasokan) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: 25-100 ton/minggu">
                    @error('kapasitas_pasokan') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('supervisor.produk.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
