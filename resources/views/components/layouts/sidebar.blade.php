<div class="flex flex-col flex-grow h-full bg-slate-900 border-r border-slate-800 text-slate-300">
    <!-- Header/Logo -->
    <div class="flex items-center justify-between h-20 px-6 bg-slate-950 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-teal text-white shadow-lg shadow-teal/30">
                <i class="fas fa-cubes text-lg"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold text-white tracking-wide leading-tight">PT WANGSA</h1>
                <p class="text-[10px] text-teal font-semibold tracking-widest uppercase">Jatra Lestari</p>
            </div>
        </div>
    </div>

    <!-- User Profile Card -->
    <div class="p-4 mx-4 mt-6 rounded-xl bg-slate-800/40 border border-slate-800/50 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-700 text-teal font-bold uppercase shadow-inner">
                {{ substr(auth()->user()?->nama ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()?->nama ?? 'Guest' }}</p>
                <span class="inline-flex items-center px-2 py-0.5 mt-0.5 rounded text-[10px] font-medium bg-teal/10 text-teal capitalize">
                    <i class="mr-1 fas fa-shield-alt text-[8px]"></i> {{ auth()->user()?->role ?? 'Guest' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 mt-6 space-y-1 overflow-y-auto">
        @if(auth()->user()?->role === 'supervisor')
            <!-- SUPERVISOR MENU -->
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Menu Utama</p>
            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.dashboard') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-chart-line text-sm mr-2 {{ request()->routeIs('supervisor.dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
                Dashboard
            </a>
            
            <p class="px-3 pt-4 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Data Master</p>
            <a href="{{ route('supervisor.supplier.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.supplier.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-truck text-sm mr-2 {{ request()->routeIs('supervisor.supplier.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Supplier
            </a>
            <a href="{{ route('supervisor.produk.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.produk.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-boxes text-sm mr-2 {{ request()->routeIs('supervisor.produk.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Produk
            </a>
            <a href="{{ route('supervisor.kriteria.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.kriteria.*') || request()->routeIs('supervisor.subkriteria.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-list-ol text-sm mr-2 {{ request()->routeIs('supervisor.kriteria.*') || request()->routeIs('supervisor.subkriteria.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Kriteria & Sub
            </a>

            <p class="px-3 pt-4 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Analisis & SPK</p>
            <a href="{{ route('supervisor.ahp.alternatif') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.ahp.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-calculator text-sm mr-2 {{ request()->routeIs('supervisor.ahp.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Perhitungan AHP
            </a>
            
            <p class="px-3 pt-4 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Laporan</p>
            <a href="{{ route('supervisor.laporan.penilaian') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.laporan.penilaian') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-file-invoice text-sm mr-2 {{ request()->routeIs('supervisor.laporan.penilaian') ? 'text-white' : 'text-slate-500' }}"></i>
                Hasil Penilaian
            </a>
            <a href="{{ route('supervisor.laporan.pengadaan') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.laporan.pengadaan') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-file-contract text-sm mr-2 {{ request()->routeIs('supervisor.laporan.pengadaan') ? 'text-white' : 'text-slate-500' }}"></i>
                Riwayat Pengadaan
            </a>
            <a href="{{ route('supervisor.laporan.kinerja') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('supervisor.laporan.kinerja') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-clipboard-list text-sm mr-2 {{ request()->routeIs('supervisor.laporan.kinerja') ? 'text-white' : 'text-slate-500' }}"></i>
                Kinerja Supplier
            </a>
        @elseif(auth()->user()?->role === 'sales')
            <!-- SALES MENU -->
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Menu Utama</p>
            <a href="{{ route('sales.dashboard') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('sales.dashboard') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-chart-line text-sm mr-2 {{ request()->routeIs('sales.dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
                Dashboard
            </a>
            <a href="{{ route('sales.purchase_order.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('sales.purchase_order.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-file-invoice-dollar text-sm mr-2 {{ request()->routeIs('sales.purchase_order.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Purchase Order
            </a>
        @elseif(auth()->user()?->role === 'logistik')
            <!-- LOGISTIK MENU -->
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Menu Utama</p>
            <a href="{{ route('logistik.dashboard') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('logistik.dashboard') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-chart-line text-sm mr-2 {{ request()->routeIs('logistik.dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
                Dashboard
            </a>
            <a href="{{ route('logistik.penerimaan.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('logistik.penerimaan.*') ? 'bg-teal text-white shadow-md shadow-teal/15 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                <i class="w-5 fas fa-dolly text-sm mr-2 {{ request()->routeIs('logistik.penerimaan.*') ? 'text-white' : 'text-slate-500' }}"></i>
                Penerimaan Barang
            </a>
        @endif
    </nav>

    <!-- Footer / Logout -->
    <div class="p-4 bg-slate-950 border-t border-slate-800/80">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center justify-center w-full px-4 py-2.5 text-xs font-semibold text-white bg-slate-800 hover:bg-red-600 transition-all duration-200 rounded-lg shadow-md group">
                <i class="mr-2 fas fa-sign-out-alt text-slate-400 group-hover:text-white transition-colors duration-200"></i>
                Keluar Aplikasi
            </button>
        </form>
    </div>
</div>
