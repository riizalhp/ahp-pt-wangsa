<x-layouts.app title="Pilih Produk sebagai Alternatif AHP">
    <!-- Stepper Navigation -->
    <x-ui.stepper active="alternatif" />

    <div class="mt-6 flex flex-col gap-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            <!-- Card Header -->
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal/15 text-teal shrink-0">
                        <i class="fas fa-boxes-stacked text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 tracking-wide">Pilih Produk Alternatif</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Pilih minimal 2 produk — suppliernya akan menjadi alternatif AHP</p>
                    </div>
                </div>

                <!-- Search bar (GET, server-side filter) -->
                <form method="GET" action="{{ route('supervisor.ahp.alternatif') }}"
                      class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Cari nama produk..."
                        class="flex-1 max-w-sm px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all outline-none text-sm text-slate-700 font-medium"
                    >
                    <button type="submit"
                            class="shrink-0 px-4 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all duration-150">
                        Cari
                    </button>
                    @if($search)
                        <a href="{{ route('supervisor.ahp.alternatif') }}"
                           class="shrink-0 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-colors">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Validation Errors -->
            @if (session('error'))
                <div class="mx-6 mt-5 p-3.5 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs font-medium flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-500 shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mx-6 mt-5 p-3.5 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs font-medium">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-exclamation-circle text-red-500 shrink-0"></i>
                        <span class="font-bold">Terjadi kesalahan validasi:</span>
                    </div>
                    <ul class="list-disc ml-6 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Product Selection Form (POST) -->
            <form action="{{ route('supervisor.ahp.alternatif') }}" method="POST" id="alternatif-form">
                @csrf

                <div class="overflow-x-auto">
                    @if($produks->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <i class="fas fa-box-open text-lg"></i>
                            </div>
                            @if($search)
                                <p class="text-sm text-slate-500 font-medium">Tidak ada produk dengan nama "<span class="font-bold">{{ $search }}</span>".</p>
                            @else
                                <p class="text-sm text-slate-500 font-medium">Belum ada data produk. Tambahkan produk terlebih dahulu.</p>
                            @endif
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80">
                                    <th class="px-4 py-3 w-10"><span class="sr-only">Pilih</span></th>
                                    <th class="px-4 py-3">Nama Supplier</th>
                                    <th class="px-4 py-3">Nama Produk</th>
                                    <th class="px-4 py-3">Jenis Produk</th>
                                    <th class="px-4 py-3">Merk</th>
                                    <th class="px-4 py-3">Ukuran</th>
                                    <th class="px-4 py-3">Alamat Supplier</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700 bg-white">
                                @foreach($produks as $produk)
                                    <tr class="hover:bg-teal/5 cursor-pointer transition-colors"
                                        data-name="{{ strtolower($produk->nama) }}"
                                        onclick="toggleRow(this)">
                                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                                            <input
                                                type="checkbox"
                                                name="selected_produk_ids[]"
                                                value="{{ $produk->id }}"
                                                id="produk_{{ $produk->id }}"
                                                class="w-4 h-4 rounded border-slate-300 text-teal focus:ring-teal/30 cursor-pointer"
                                                {{ in_array($produk->id, old('selected_produk_ids', [])) ? 'checked' : '' }}
                                                onchange="updateCounter()"
                                            >
                                        </td>
                                        <td class="px-4 py-3 font-bold text-slate-800">{{ $produk->supplier->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 font-bold text-teal-dark">{{ $produk->nama }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $produk->jenis_produk ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $produk->merk ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $produk->ukuran ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $produk->supplier->alamat ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Footer: counter + submit -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs">
                        <i class="fas fa-check-square text-teal"></i>
                        <span>
                            <span id="count" class="font-bold text-slate-700">0</span>
                            <span class="text-slate-500"> produk dipilih</span>
                            <span id="hint" class="ml-1 text-amber-600 font-medium">(pilih minimal 2)</span>
                        </span>
                    </div>
                    <button
                        type="submit"
                        id="submit-btn"
                        disabled
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md shadow-teal/20 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                        Lanjut ke Perbandingan <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function toggleRow(row) {
            const checkbox = row.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateCounter();
            }
        }

        function updateCounter() {
            const checked = document.querySelectorAll('input[name="selected_produk_ids[]"]:checked').length;
            document.getElementById('count').textContent = checked;
            const btn = document.getElementById('submit-btn');
            const hint = document.getElementById('hint');
            const ready = checked >= 2;
            if (btn) btn.disabled = !ready;
            if (hint) {
                hint.textContent = ready ? '' : '(pilih minimal 2)';
                hint.className = ready ? '' : 'ml-1 text-amber-600 font-medium';
            }
        }

        document.addEventListener('DOMContentLoaded', updateCounter);
    </script>
</x-layouts.app>
