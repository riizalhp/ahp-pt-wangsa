<x-layouts.app title="Proses Aktual Penerimaan Barang">
    <div class="mb-6">
        <a href="{{ route('logistik.aktual.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- PO Details Summary Box -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm lg:col-span-2 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Detail Purchase Order</h3>
            <div class="grid grid-cols-2 gap-4 text-xs font-medium text-slate-600">
                <div>
                    <span class="block text-slate-400 font-bold mb-1">Nomor PO</span>
                    <span class="text-sm font-bold text-slate-800">#{{ $pengadaan->id }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold mb-1">Tanggal PO</span>
                    <span class="text-sm font-bold text-slate-800">{{ $pengadaan->tanggal_po->isoFormat('D MMMM Y') }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold mb-1">Supplier</span>
                    <span class="text-sm font-bold text-slate-850">{{ $pengadaan->supplier->nama }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold mb-1">Produk</span>
                    <span class="text-sm font-bold text-slate-850">{{ $pengadaan->produk->nama }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold mb-1">Jumlah Dipesan (Target)</span>
                    <span class="text-sm font-extrabold text-teal-dark">{{ number_format($pengadaan->jumlah_dibeli) }} {{ $pengadaan->produk->satuan }}</span>
                </div>
                @if($pengadaan->catatan)
                    <div class="col-span-2">
                        <span class="block text-slate-400 font-bold mb-1">Catatan PO</span>
                        <p class="p-2 rounded bg-slate-50 border border-slate-100 text-[11px] leading-relaxed italic text-slate-500">{{ $pengadaan->catatan }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Documentation Photo -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Foto Laporan PO</h3>
            <div class="flex-1 flex items-center justify-center border border-slate-100 rounded-xl overflow-hidden bg-slate-50 min-h-[120px]">
                @if($pengadaan->foto_path)
                    <a href="{{ asset($pengadaan->foto_path) }}" target="_blank">
                        <img src="{{ asset($pengadaan->foto_path) }}" alt="Foto PO" class="max-h-[140px] w-auto hover:scale-105 transition-transform">
                    </a>
                @else
                    <div class="text-slate-400 text-xs text-center flex flex-col items-center gap-1">
                        <i class="fas fa-image text-lg text-slate-300"></i>
                        <span class="italic">Tidak ada foto dokumentasi</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actual Reception Input Form -->
    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" 
         x-data="{ 
             dibeli: {{ $pengadaan->jumlah_dibeli }}, 
             diterima: {{ old('jumlah_diterima', $pengadaan->jumlah_diterima ?? $pengadaan->jumlah_dibeli) }},
             cacat: {{ old('jumlah_cacat', $pengadaan->jumlah_cacat ?? 0) }}
         }">
        <form action="{{ route('logistik.aktual.update', $pengadaan->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Input Realisasi Penerimaan Barang</h3>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tanggal_kedatangan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Kedatangan Aktual <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_kedatangan" id="tanggal_kedatangan" required 
                           value="{{ old('tanggal_kedatangan', $pengadaan->tanggal_kedatangan ? $pengadaan->tanggal_kedatangan->format('Y-m-d') : date('Y-m-d')) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                    @error('tanggal_kedatangan') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="jumlah_diterima" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Diterima Aktual <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_diterima" id="jumlah_diterima" required 
                           x-model.number="diterima" :max="dibeli" min="0"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Maksimum: {{ $pengadaan->jumlah_dibeli }}">
                    <span class="text-[10px] text-slate-400 mt-1 block">Tidak boleh melebihi jumlah PO ({{ $pengadaan->jumlah_dibeli }}).</span>
                    @error('jumlah_diterima') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="jumlah_cacat" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Barang Cacat/Rusak <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_cacat" id="jumlah_cacat" required 
                           x-model.number="cacat" :max="diterima" min="0"
                           class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium"
                           placeholder="Maksimum: jumlah diterima">
                    <span class="text-[10px] text-slate-400 mt-1 block">Tidak boleh melebihi jumlah diterima.</span>
                    @error('jumlah_cacat') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Client-side error prompts -->
            <template x-if="diterima > dibeli">
                <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl flex items-center gap-2 font-medium">
                    <i class="fas fa-times-circle text-red-500"></i>
                    <span>Kesalahan: Jumlah diterima tidak boleh lebih besar dari jumlah dipesan!</span>
                </div>
            </template>
            <template x-if="cacat > diterima">
                <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl flex items-center gap-2 font-medium">
                    <i class="fas fa-times-circle text-red-500"></i>
                    <span>Kesalahan: Jumlah barang cacat tidak boleh lebih besar dari jumlah diterima!</span>
                </div>
            </template>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('logistik.aktual.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                    Batalkan
                </a>
                <button type="submit" 
                        ::disabled="diterima > dibeli || cacat > diterima"
                        class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-save mr-1"></i> Simpan Penerimaan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
