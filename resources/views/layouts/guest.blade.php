<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Softlinkia') }}</title>

        <!-- Google Fonts: Outfit -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#f8fafc] bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px]">
            
            <div class="mb-10 animate-in fade-in zoom-in duration-700">
                <a href="/" wire:navigate class="flex flex-col items-center gap-2">
                    <div class="p-4 bg-indigo-600 rounded-[2rem] shadow-2xl shadow-indigo-200 group hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <span class="text-xl font-black tracking-tighter text-gray-800 uppercase">Softlinkia</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-10 py-12 glass border-gray-200/50 rounded-[3rem] shadow-2xl premium-card overflow-hidden transition-all animate-in slide-in-from-bottom-8 duration-700">
                {{ $slot }}
            </div>

            <p class="mt-12 text-[9px] font-bold text-gray-400 uppercase tracking-widest animate-in fade-in duration-1000 delay-500">
                Monitorización de Seguridad de Grado Empresarial
            </p>
        </div>
    </body>
</html>
