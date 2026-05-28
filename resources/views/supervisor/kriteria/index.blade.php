<x-layouts.app title="Kelola Kriteria & Subkriteria">
    <div class="flex items-center justify-between mb-6">
        <p class="text-xs text-slate-500 font-medium">Halaman untuk mengelola kriteria penilaian AHP serta subkriteria turunannya.</p>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.kriteria.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal text-white text-xs font-bold hover:bg-teal-dark shadow-md transition-all">
                <i class="fas fa-plus"></i> Kriteria Baru
            </a>
            <a href="{{ route('supervisor.subkriteria.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-teal text-teal hover:bg-teal/5 text-xs font-bold transition-all">
                <i class="fas fa-circle-plus"></i> Subkriteria Baru
            </a>
        </div>
    </div>

    <!-- Grid Layout: Criteria List & Subcriteria List Grouped -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- 1. Kriteria Table Card (1/3 width on desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-1 h-fit">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Kriteria Utama</h3>
            </div>
            <div class="p-4">
                @if($kriterias->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada kriteria.</p>
                @else
                    <div class="space-y-3">
                        @foreach($kriterias as $kriteria)
                            <div class="p-3 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal/15 text-teal-dark uppercase">{{ $kriteria->kode }}</span>
                                        <span class="text-xs font-bold text-slate-800">{{ $kriteria->nama }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[150px]">{{ $kriteria->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('supervisor.kriteria.edit', $kriteria->id) }}" class="p-1.5 rounded-lg bg-slate-50 text-slate-500 hover:bg-teal hover:text-white transition-colors" title="Edit">
                                        <i class="fas fa-pen text-[9px]"></i>
                                    </a>
                                    <form action="{{ route('supervisor.kriteria.destroy', $kriteria->id) }}" method="POST" onsubmit="return confirm('Hapus kriteria ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-slate-50 text-red-500 hover:bg-red-600 hover:text-white transition-colors" title="Hapus">
                                            <i class="fas fa-trash text-[9px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Subcriteria Grouped List (2/3 width on desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden lg:col-span-2">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Subkriteria Per Kriteria (Grouped)</h3>
            </div>
            <div class="p-6 space-y-6">
                @if($kriterias->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-6">Silakan tambahkan kriteria terlebih dahulu.</p>
                @else
                    @foreach($kriterias as $kriteria)
                        @php
                            $subs = $subkriterias->get($kriteria->id) ?? collect();
                        @endphp
                        <div class="rounded-xl border border-slate-200/60 overflow-hidden shadow-inner bg-slate-50/20">
                            <!-- Accordion/Header -->
                            <div class="px-4 py-3 bg-slate-50 flex items-center justify-between border-b border-slate-200/60">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 uppercase">{{ $kriteria->kode }}</span>
                                    <span class="text-xs font-bold text-slate-700">{{ $kriteria->nama }}</span>
                                    <span class="ml-2 text-[10px] font-medium text-slate-400">({{ $subs->count() }} subkriteria)</span>
                                </div>
                                <a href="{{ route('supervisor.subkriteria.create', ['kriteria_id' => $kriteria->id]) }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-teal hover:text-teal-dark">
                                    <i class="fas fa-plus"></i> Tambah Sub
                                </a>
                            </div>

                            <!-- Subcriteria list -->
                            <div class="p-4 bg-white">
                                @if($subs->isEmpty())
                                    <p class="text-xs text-slate-400 italic py-2">Belum ada subkriteria untuk kriteria ini.</p>
                                @else
                                    <table class="min-w-full divide-y divide-slate-100">
                                        <thead>
                                            <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                <th class="pb-2">Kode</th>
                                                <th class="pb-2">Nama Subkriteria</th>
                                                <th class="pb-2">Deskripsi</th>
                                                <th class="pb-2 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-xs">
                                            @foreach($subs as $sub)
                                                <tr>
                                                    <td class="py-2.5 font-bold text-slate-500 uppercase">{{ $sub->kode }}</td>
                                                    <td class="py-2.5 font-semibold text-slate-800">{{ $sub->nama }}</td>
                                                    <td class="py-2.5 text-slate-400 max-w-xs truncate">{{ $sub->deskripsi ?? '-' }}</td>
                                                    <td class="py-2.5 text-right">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <a href="{{ route('supervisor.subkriteria.edit', $sub->id) }}" class="p-1 rounded bg-slate-50 text-slate-500 hover:bg-teal hover:text-white transition-colors" title="Edit">
                                                                <i class="fas fa-pen text-[8px]"></i>
                                                            </a>
                                                            <form action="{{ route('supervisor.subkriteria.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus subkriteria ini?')" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="p-1 rounded bg-slate-50 text-red-500 hover:bg-red-600 hover:text-white transition-colors" title="Hapus">
                                                                    <i class="fas fa-trash text-[8px]"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
