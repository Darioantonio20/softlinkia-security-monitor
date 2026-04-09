<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Softlinkia | Bienvenido</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased selection:bg-indigo-500 selection:text-white">
        <div class="flex flex-col items-center justify-center min-h-screen bg-[#f8fafc] bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:16px_16px]">
            
            <div class="liquid-glass p-16 rounded-[4rem] flex flex-col items-center text-center shadow-2xl max-w-2xl mx-4">
                <div class="mb-8 p-4 bg-indigo-600 rounded-[2rem] shadow-2xl shadow-indigo-200">
                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>

                <h1 class="text-6xl font-black text-gray-800 mb-4 tracking-tighter">SOFTLINKIA</h1>
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.4em] mb-12">Security Monitor — Enterprise v1.0</p>
                
                <div class="h-px w-24 bg-gray-200 mb-12"></div>

                @if (Route::has('login'))
                    <div class="flex flex-wrap justify-center gap-6">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-[0_15px_30px_-5px_rgba(79,70,229,0.4)] hover:shadow-[0_20px_40px_-5px_rgba(79,70,229,0.5)] transition-all hover:-translate-y-1 active:scale-95">Ir al Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-[0_15px_30px_-5px_rgba(79,70,229,0.4)] hover:shadow-[0_20px_40px_-5px_rgba(79,70,229,0.5)] transition-all hover:-translate-y-1 active:scale-95">Acceder</a>
                            
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-10 py-4 glass border-gray-300/50 text-gray-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-white transition-all hover:-translate-y-1 active:scale-95">Registro</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>

            <p class="mt-12 text-[9px] font-bold text-gray-400 uppercase tracking-widest">© {{ date('Y') }} Softlinkia. Todos los derechos reservados.</p>
        </div>
    </body>
</html>
