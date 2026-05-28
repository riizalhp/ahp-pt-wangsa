<x-layouts.app title="Tambah Kriteria Baru">
    <div class="mb-6">
        <a href="{{ route('supervisor.kriteria.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('supervisor.kriteria.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="kode" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kode Kriteria <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" id="kode" required value="{{ old('kode') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: C (Cost), Q (Quality)">
                    @error('kode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nama" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Kriteria <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Contoh: Cost / Biaya">
                    @error('nama') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Deskripsi Kriteria</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                          class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                          placeholder="Masukkan rincian penjelasan kriteria">{{ old('deskripsi') }}</textarea>
                @error('deskripsi') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('supervisor.kriteria.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                    <i class="fas fa-save mr-1"></i> Simpan Kriteria
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
