<x-layouts.app title="Input Penerimaan Barang - {{ $penerimaan->no_po }}">
    <div class="mb-6">
        <a href="{{ route('logistik.penerimaan.index') }}" class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- PO Header Info -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Detail Purchase Order</h3>
        <div class="grid grid-cols-2 gap-4 text-xs font-medium text-slate-600 sm:grid-cols-3 lg:grid-cols-5">
            <div>
                <span class="block text-slate-400 font-bold mb-1">Nomor PO</span>
                <span class="text-sm font-bold text-slate-800">{{ $penerimaan->no_po }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-bold mb-1">Supplier</span>
                <span class="text-sm font-bold text-slate-800">{{ $penerimaan->supplier->nama }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-bold mb-1">Tanggal PO</span>
                <span class="text-sm font-bold text-slate-800">{{ $penerimaan->tanggal_po->isoFormat('D MMMM Y') }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-bold mb-1">Target Kedatangan</span>
                <span class="text-sm font-bold text-slate-800">
                    @if($penerimaan->tanggal_kedatangan_target)
                        {{ $penerimaan->tanggal_kedatangan_target->isoFormat('D MMMM Y') }}
                    @else
                        <span class="italic text-slate-400">—</span>
                    @endif
                </span>
            </div>
            @if($penerimaan->catatan)
                <div class="col-span-2 sm:col-span-3 lg:col-span-5">
                    <span class="block text-slate-400 font-bold mb-1">Catatan</span>
                    <p class="p-2 rounded bg-slate-50 border border-slate-100 text-[11px] leading-relaxed italic text-slate-500">{{ $penerimaan->catatan }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="p-4 mb-6 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <div>
                    <p class="text-xs font-bold text-red-700 mb-1">Terdapat kesalahan pada input:</p>
                    <ul class="text-xs text-red-600 space-y-0.5 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Line Items Reception Form -->
    <form action="{{ route('logistik.penerimaan.update', $penerimaan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            @foreach($penerimaan->detail as $detail)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <!-- Product Header -->
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <span class="text-sm font-bold text-slate-800">{{ $detail->produk->nama }}</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($detail->produk->jenis_produk)
                                    <span class="text-[11px] text-slate-500 font-medium">{{ $detail->produk->jenis_produk }}</span>
                                @endif
                                @if($detail->produk->merk)
                                    <span class="text-[11px] text-slate-400">•</span>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ $detail->produk->merk }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Jumlah Dipesan</span>
                            <p class="text-sm font-extrabold text-teal-dark">
                                {{ number_format($detail->jumlah_dipesan, 2) }} {{ $detail->satuan }}
                            </p>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Jumlah Diterima Baik -->
                        <div>
                            <label for="jumlah_diterima_baik_{{ $detail->id }}"
                                   class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Jumlah Diterima (Baik) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                id="jumlah_diterima_baik_{{ $detail->id }}"
                                name="items[{{ $detail->id }}][jumlah_diterima_baik]"
                                step="0.01"
                                min="0"
                                max="{{ $detail->jumlah_dipesan }}"
                                required
                                value="{{ old('items.' . $detail->id . '.jumlah_diterima_baik', $detail->jumlah_diterima_baik ?? '') }}"
                                placeholder="Maks: {{ number_format($detail->jumlah_dipesan, 2) }}"
                                class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium @error('items.' . $detail->id . '.jumlah_diterima_baik') border-red-400 bg-red-50 @enderror"
                            >
                            <span class="text-[10px] text-slate-400 mt-1 block">
                                Tidak boleh melebihi jumlah dipesan ({{ number_format($detail->jumlah_dipesan, 2) }} {{ $detail->satuan }}).
                            </span>
                            @error('items.' . $detail->id . '.jumlah_diterima_baik')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tanggal Kedatangan Aktual -->
                        <div>
                            <label for="tanggal_kedatangan_aktual_{{ $detail->id }}"
                                   class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Tanggal Kedatangan Aktual <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="tanggal_kedatangan_aktual_{{ $detail->id }}"
                                name="items[{{ $detail->id }}][tanggal_kedatangan_aktual]"
                                required
                                value="{{ old('items.' . $detail->id . '.tanggal_kedatangan_aktual', $detail->tanggal_kedatangan_aktual ? $detail->tanggal_kedatangan_aktual->format('Y-m-d') : '') }}"
                                class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium @error('items.' . $detail->id . '.tanggal_kedatangan_aktual') border-red-400 bg-red-50 @enderror"
                            >
                            @error('items.' . $detail->id . '.tanggal_kedatangan_aktual')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit -->
        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('logistik.penerimaan.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                Batalkan
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                <i class="fas fa-save mr-1"></i> Simpan Penerimaan
            </button>
        </div>
    </form>
</x-layouts.app>
