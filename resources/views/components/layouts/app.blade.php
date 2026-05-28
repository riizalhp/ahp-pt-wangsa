<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('APP_NAME', 'SPK Pemilihan Supplier') }}</title>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS & FontAwesome -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full font-sans antialiased text-slate-700 bg-slate-50" x-data="{ sidebarOpen: false }">

    @auth
        <div class="min-h-full">
            <!-- Sidebar for Desktop -->
            <aside class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
                <x-layouts.sidebar />
            </aside>

            <!-- Mobile Drawer Sidebar -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="relative z-50 lg:hidden" 
                 role="dialog" 
                 aria-modal="true"
                 style="display: none;">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-teal-900/40 backdrop-blur-sm" @click="sidebarOpen = false"></div>

                <div class="fixed inset-0 flex">
                    <div x-show="sidebarOpen"
                         x-transition:enter="transition ease-in-out duration-300 transform"
                         x-transition:enter-start="-translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in-out duration-300 transform"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="-translate-x-full"
                         class="relative flex flex-col flex-1 w-full max-w-xs bg-white"
                         @click.away="sidebarOpen = false">
                        
                        <!-- Close button -->
                        <div class="absolute top-0 right-0 flex justify-center w-16 pt-4 -mr-16">
                            <button type="button" class="flex items-center justify-center w-10 h-10 rounded-full focus:outline-none focus:ring-2 focus:ring-white" @click="sidebarOpen = false">
                                <span class="sr-only">Close sidebar</span>
                                <i class="text-xl text-white fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Sidebar content for mobile -->
                        <x-layouts.sidebar />
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex flex-col lg:pl-72">
                <!-- Navbar -->
                <x-layouts.navbar />

                <!-- Main Content -->
                <main class="py-10">
                    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <!-- Session Alert -->
                        @if (session('success'))
                            <div class="p-4 mb-6 rounded-md bg-teal-50 border-l-4 border-teal text-teal-dark" x-data="{ show: true }" x-show="show">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i class="mr-3 fas fa-check-circle text-teal"></i>
                                        <span>{{ session('success') }}</span>
                                    </div>
                                    <button @click="show = false" class="text-teal-dark hover:text-teal">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="p-4 mb-6 rounded-md bg-red-50 border-l-4 border-red-500 text-red-700" x-data="{ show: true }" x-show="show">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i class="mr-3 fas fa-exclamation-circle text-red-500"></i>
                                        <span>{{ session('error') }}</span>
                                    </div>
                                    <button @click="show = false" class="text-red-700 hover:text-red-900">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    @else
        <div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8 bg-cover bg-center" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(13, 148, 136, 0.85));">
            <div class="max-w-md w-full space-y-8 bg-white/95 backdrop-blur-md p-10 rounded-2xl shadow-2xl border border-white/20">
                {{ $slot }}
            </div>
    @endauth

</body>
</html>
