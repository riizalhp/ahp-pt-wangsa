<x-layouts.app title="Buat Purchase Order Baru">
    <div class="mb-6">
        <a href="{{ route('admin_purchasing.purchase_order.index') }}"
           class="text-xs font-bold text-teal hover:text-teal-dark flex items-center gap-1 w-fit">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- Global validation errors summary --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-sm font-bold text-red-700 mb-1">Terdapat kesalahan pada formulir:</p>
                    <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin_purchasing.purchase_order.store') }}" method="POST" enctype="multipart/form-data"
          x-data="{
              items: {{ old('items') ? json_encode(old('items')) : '[{produk_id: \'\', jumlah_dipesan: \'\', satuan: \'\'}]' }},
              satuanList: @js($satuanList),
              selectedSupplierId: '{{ old('supplier_id', '') }}',
              allProducts: @js($produks),
              get filteredProducts() {
                  if (!this.selectedSupplierId) return this.allProducts;
                  return this.allProducts.filter(p => p.supplier_id == this.selectedSupplierId);
              },
              addItem() {
                  this.items.push({ produk_id: '', jumlah_dipesan: '', satuan: '' });
              },
              removeItem(index) {
                  if (this.items.length > 1) this.items.splice(index, 1);
              },
              onSupplierChange() {
                  // Reset all product selections when supplier changes
                  this.items.forEach(item => {
                      item.produk_id = '';
                  });
              }
          }">
        @csrf

        {{-- ── Header Section ── --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-teal text-base"></i>
                    Informasi Purchase Order
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2">

                {{-- Supplier --}}
                <div class="sm:col-span-2">
                    <label for="supplier_id"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Pilih Supplier <span class="text-red-500">*</span>
                    </label>
                    <select name="supplier_id" id="supplier_id" required
                            x-model="selectedSupplierId"
                            @change="onSupplierChange()"
                            class="block w-full px-4 py-2.5 rounded-xl border @error('supplier_id') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                [{{ $s->kode }}] {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. PO --}}
                <div>
                    <label for="no_po"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Nomor PO <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="no_po" id="no_po" required
                           value="{{ old('no_po') }}"
                           placeholder="Contoh: PO/202601/0001"
                           class="block w-full px-4 py-2.5 rounded-xl border @error('no_po') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium font-mono">
                    @error('no_po')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal PO --}}
                <div>
                    <label for="tanggal_po"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Tanggal PO <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_po" id="tanggal_po" required
                           value="{{ old('tanggal_po', date('Y-m-d')) }}"
                           class="block w-full px-4 py-2.5 rounded-xl border @error('tanggal_po') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                    @error('tanggal_po')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Kedatangan Target --}}
                <div>
                    <label for="tanggal_kedatangan_target"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Tanggal Kedatangan Target <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_kedatangan_target" id="tanggal_kedatangan_target" required
                           value="{{ old('tanggal_kedatangan_target') }}"
                           class="block w-full px-4 py-2.5 rounded-xl border @error('tanggal_kedatangan_target') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                    @error('tanggal_kedatangan_target')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="sm:col-span-2">
                    <label for="catatan"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Catatan <span class="text-slate-400 font-normal normal-case">(opsional)</span>
                    </label>
                    <textarea name="catatan" id="catatan" rows="3"
                              placeholder="Instruksi khusus atau catatan untuk supplier..."
                              class="block w-full px-4 py-2.5 rounded-xl border @error('catatan') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium resize-none">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Foto --}}
                <div class="sm:col-span-2">
                    <label for="foto"
                           class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Foto Lampiran <span class="text-slate-400 font-normal normal-case">(opsional)</span>
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/jpg,image/png"
                           class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer border @error('foto') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl focus:outline-none focus:border-teal">
                    <p class="mt-1 text-xs text-slate-400">Format: JPG, JPEG, PNG. Maksimal 2MB.</p>
                    @error('foto')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ── Line Items Section ── --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-list-ul text-teal text-base"></i>
                    Detail Produk
                </h2>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal/10 text-teal text-xs font-bold hover:bg-teal hover:text-white transition-colors duration-150">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Produk
                </button>
            </div>

            @error('items')
                <div class="px-6 pt-4">
                    <p class="text-xs text-red-500 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </p>
                </div>
            @enderror

            <div class="p-6 overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <th class="pb-3 pr-3 w-8">No.</th>
                            <th class="pb-3 pr-3">Produk <span class="text-red-400">*</span></th>
                            <th class="pb-3 pr-3 w-36">Jumlah Dipesan <span class="text-red-400">*</span></th>
                            <th class="pb-3 pr-3 w-36">Satuan <span class="text-red-400">*</span></th>
                            <th class="pb-3 w-10 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="group">
                                {{-- Row number --}}
                                <td class="py-3 pr-3 text-xs text-slate-400 font-medium align-top pt-4">
                                    <span x-text="index + 1"></span>.
                                </td>

                                {{-- Produk select --}}
                                <td class="py-3 pr-3 align-top">
                                    <select :name="`items[${index}][produk_id]`" x-model="item.produk_id" required
                                            class="block w-full min-w-[220px] px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                                        <option value="">-- Pilih Produk --</option>
                                        <template x-if="!selectedSupplierId">
                                            <option value="" disabled>Pilih supplier terlebih dahulu</option>
                                        </template>
                                        <template x-for="p in filteredProducts" :key="p.id">
                                            <option :value="p.id" x-text="p.nama + ' - ' + p.ukuran + ' - ' + p.merk"></option>
                                        </template>
                                    </select>
                                </td>

                                {{-- Jumlah Dipesan --}}
                                <td class="py-3 pr-3 align-top">
                                    <input type="number" :name="`items[${index}][jumlah_dipesan]`"
                                           x-model="item.jumlah_dipesan"
                                           step="0.01" min="0.01" required
                                           placeholder="0.00"
                                           class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                                </td>

                                {{-- Satuan combobox (datalist) --}}
                                <td class="py-3 pr-3 align-top">
                                    <input type="text" :name="`items[${index}][satuan]`"
                                           x-model="item.satuan"
                                           :list="`satuan-list-${index}`"
                                           required
                                           placeholder="Pilih atau ketik..."
                                           autocomplete="off"
                                           class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-800 font-medium">
                                    <datalist :id="`satuan-list-${index}`">
                                        <template x-for="s in satuanList" :key="s">
                                            <option :value="s"></option>
                                        </template>
                                    </datalist>
                                </td>

                                {{-- Remove row button --}}
                                <td class="py-3 text-center align-top pt-2.5">
                                    <button type="button"
                                            @click="removeItem(index)"
                                            :disabled="items.length === 1"
                                            :class="items.length === 1
                                                ? 'text-slate-300 cursor-not-allowed'
                                                : 'text-red-400 hover:text-white hover:bg-red-500 cursor-pointer'"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 transition-colors duration-150"
                                            title="Hapus baris">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-6 pb-5">
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-2 text-xs font-bold text-teal hover:text-teal-dark transition-colors">
                    <i class="fas fa-circle-plus"></i> + Tambah Produk
                </button>
            </div>
        </div>

        {{-- ── Form Actions ── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin_purchasing.purchase_order.index') }}"
               class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                Batalkan
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/10 hover:shadow-teal/20 transition-all duration-150">
                <i class="fas fa-save"></i> Simpan Purchase Order
            </button>
        </div>

    </form>
</x-layouts.app>
