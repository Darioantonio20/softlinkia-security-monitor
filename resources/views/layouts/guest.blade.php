<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Softlinkia') }} | Seguridad</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 border-none">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#f8fafc] relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute inset-0 z-0">
                <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/10 rounded-full blur-[120px]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px] opacity-40"></div>
            </div>

            <div class="z-10 w-full sm:max-w-md mt-6">
                <!-- Logo Section -->
                <div class="flex flex-col items-center mb-10 group">
                    <a href="/" wire:navigate class="relative">
                        <div class="absolute -inset-4 bg-indigo-500/20 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <x-application-logo class="w-16 h-16 fill-current text-indigo-600 relative drop-shadow-2xl" />
                    </a>
                    <h1 class="mt-4 text-[12px] font-black uppercase tracking-[0.4em] text-slate-400">Security Management</h1>
                </div>

                <!-- Main Card -->
                <div class="w-full px-10 py-12 bg-white/70 backdrop-blur-2xl border border-white/50 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] sm:rounded-[2.5rem] relative overflow-hidden">
                    <!-- Top Shine -->
                    <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-white to-transparent"></div>
                    
                    {{ $slot }}
                </div>

                <!-- Footer Info -->
                <div class="mt-12 text-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }} Security Platform. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </body>
</html>
