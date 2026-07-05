<x-layouts.app title="Tambah Produk Baru">
    <div class="mb-6">
        <a href="{{ route('supervisor.produk.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('supervisor.produk.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Supplier -->
                <div class="sm:col-span-2">
                    <label for="supplier_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" id="supplier_id" required
                            class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Nama Produk -->
                <div class="sm:col-span-2">
                    <label for="nama" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Duplex Coat 310 gr">
                    @error('nama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Jenis Produk -->
                <div>
                    <label for="jenis_produk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Produk</label>
                    <input type="text" name="jenis_produk" id="jenis_produk" value="{{ old('jenis_produk') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Kertas">
                    @error('jenis_produk') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Merk -->
                <div>
                    <label for="merk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Merk <span class="text-red-500">*</span></label>
                    <input type="text" name="merk" id="merk" required value="{{ old('merk') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Hansol">
                </div>

                <!-- Error untuk kombinasi nama+merk+ukuran (full width) -->
                @error('merk')
                    <div class="sm:col-span-2 -mt-3">
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                            <span class="text-xs text-red-600 font-medium">{{ $message }}</span>
                        </div>
                    </div>
                @enderror

                <!-- Ukuran -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ukuran</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <input type="text" name="lebar" id="lebar" value="{{ old('lebar') }}"
                                   class="block w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium text-center"
                                   placeholder="">
                        </div>
                        <div>
                            <input type="text" name="panjang" id="panjang" value="{{ old('panjang') }}"
                                   class="block w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium text-center"
                                   placeholder="">
                        </div>
                        <div>
                            <input type="text" name="tinggi" id="tinggi" value="{{ old('tinggi') }}"
                                   class="block w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium text-center"
                                   placeholder="">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Isi lebar x panjang untuk item kertas dan kardus atau panjang x lebar x tinggi sesuai jenis item.</p>
                    @error('lebar') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @error('panjang') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @error('tinggi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Kapasitas Pasokan -->
                <div class="sm:col-span-2">
                    <label for="kapasitas_nilai" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kapasitas Pasokan</label>
                    <div class="flex gap-3">
                        <input type="number" step="any" min="0" name="kapasitas_nilai" id="kapasitas_nilai" value="{{ old('kapasitas_nilai') }}"
                               class="block flex-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                               placeholder="Contoh: 100">
                        <select name="kapasitas_satuan" id="kapasitas_satuan"
                                class="w-40 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                            @foreach(['kg', 'gram', 'ton', 'kuintal', 'lembar', 'rim', 'pcs', 'roll'] as $satuan)
                                <option value="{{ $satuan }}" {{ old('kapasitas_satuan') == $satuan ? 'selected' : '' }}>{{ $satuan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('kapasitas_nilai') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @error('kapasitas_satuan') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('supervisor.produk.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                    <i class="fas fa-save mr-1"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
