<x-layouts.app title="Masuk ke Sistem SPK">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-teal text-white shadow-xl shadow-teal/30 mb-4 animate-bounce">
            <i class="fas fa-cubes text-3xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">PT Wangsa Jatra Lestari</h2>
        <p class="text-sm text-slate-500 font-medium mt-1">Sistem Pendukung Keputusan Pemilihan Supplier (AHP)</p>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border-l-4 border-red-500 text-red-700 text-xs mt-6 space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center">
                    <i class="fas fa-times-circle mr-2 text-red-500"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="username" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input id="username" name="username" type="text" required value="{{ old('username') }}"
                           class="block w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all duration-200 outline-none text-sm text-slate-800 font-medium" 
                           placeholder="Masukkan username Anda">
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                <div class="relative" x-data="{ showPass: false }">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" required 
                           class="block w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal focus:ring-4 focus:ring-teal/15 transition-all duration-200 outline-none text-sm text-slate-800 font-medium" 
                           placeholder="••••••••">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" 
                    class="group relative w-full flex justify-center py-3 px-4 rounded-xl border border-transparent text-sm font-bold text-white bg-teal hover:bg-teal-dark focus:outline-none focus:ring-4 focus:ring-teal/20 transition-all duration-350 shadow-lg shadow-teal/20 hover:shadow-teal/30 hover:-translate-y-0.5">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <i class="fas fa-sign-in-alt text-teal-light group-hover:scale-110 transition-transform duration-200"></i>
                </span>
                Masuk ke Akun
            </button>
        </div>
    </form>

    <div class="pt-4 border-t border-slate-100 mt-6 text-center">
        <p class="text-[11px] text-slate-400 font-medium">Hak Cipta © 2026 PT Wangsa Jatra Lestari</p>
    </div>
</x-layouts.app>
