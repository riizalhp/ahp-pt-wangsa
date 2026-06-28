<header class="sticky top-0 z-40 flex items-center justify-between h-20 px-4 bg-white border-b border-slate-200/80 shadow-sm sm:px-6 lg:px-8 backdrop-blur-md bg-white/95">
    <!-- Left side: Toggle button for mobile and Page Header -->
    <div class="flex items-center gap-4">
        <button type="button" 
                class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal lg:hidden"
                @click="sidebarOpen = true">
            <span class="sr-only">Open sidebar</span>
            <i class="text-lg fas fa-bars"></i>
        </button>
        
        <div>
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                @if(auth()->user()?->role === 'supervisor')
                    Supervisor Procurement Panel
                @elseif(auth()->user()?->role === 'sales')
                    Administrator Purchasing Panel
                @elseif(auth()->user()?->role === 'logistik')
                    Staff Logistik Panel
                @else
                    Sistem SPK AHP
                @endif
            </h2>
            <p class="text-lg font-bold text-slate-800 leading-tight">
                {{ $title ?? 'Dashboard Utama' }}
            </p>
        </div>
    </div>

    <!-- Right side: Date and User status -->
    <div class="flex items-center gap-4">
        <!-- Date display (Desktop only) -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600">
            <i class="text-teal fas fa-calendar-alt"></i>
            <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
        </div>

        <!-- User Dropdown Menu -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button type="button" 
                    class="flex items-center gap-2 p-1.5 rounded-full hover:bg-slate-100 transition-colors duration-150 focus:outline-none" 
                    @click="open = !open">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-teal text-white font-bold text-sm uppercase shadow-sm">
                    {{ substr(auth()->user()?->nama ?? 'U', 0, 1) }}
                </div>
                <span class="hidden sm:block text-xs font-semibold text-slate-700 pr-1">
                    {{ auth()->user()?->nama ?? 'Guest' }}
                </span>
                <i class="hidden sm:block text-[10px] text-slate-400 fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>

            <!-- Dropdown Card -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 z-50 w-48 mt-2 origin-top-right bg-white border border-slate-200/80 rounded-xl shadow-lg ring-1 ring-black/5 divide-y divide-slate-100 focus:outline-none"
                 style="display: none;">
                
                <div class="px-4 py-3">
                    <p class="text-xs text-slate-400">Username</p>
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()?->username ?? 'guest' }}</p>
                </div>

                <div class="py-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-2 text-xs text-left text-red-600 hover:bg-red-50 transition-colors duration-150">
                            <i class="w-4 mr-2 text-center fas fa-sign-out-alt"></i>
                            Keluar Aplikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
