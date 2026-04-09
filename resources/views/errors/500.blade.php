<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Error de Infraestructura | Softlinkia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .liquid-bg {
            background: radial-gradient(circle at 50% 50%, #e11d48 0%, #0f172a 100%);
        }
    </style>
</head>
<body class="liquid-bg min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-rose-500 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500 rounded-full blur-[120px] animate-pulse" style="animation-delay: 1s"></div>
    </div>

    <div class="relative z-10 text-center max-w-2xl">
        <p class="text-[14px] font-black text-rose-300 uppercase tracking-[0.5em] mb-8 animate-pulse">Error de Núcleo Detectado</p>
        <h1 class="text-[150px] font-black text-white leading-none tracking-tighter mb-4 opacity-90">500</h1>
        <div class="h-1.5 w-24 bg-rose-500 mx-auto rounded-full mb-10 shadow-[0_0_20px_rgba(225,29,72,0.8)]"></div>
        
        <h2 class="text-3xl font-black text-white mb-6">Inestabilidad en el Servidor</h2>
        <p class="text-rose-100/70 text-lg font-medium mb-12 leading-relaxed">
            Hemos detectado una anomalía interna crítica en nuestros sistemas de procesamiento. <br>
            Nuestros ingenieros de seguridad han sido notificados automáticamente.
        </p>

        <a href="/" class="inline-flex items-center px-12 py-5 bg-white text-slate-900 rounded-[2rem] font-black text-xs uppercase tracking-[0.2em] shadow-2xl hover:bg-rose-50 transition-all hover:-translate-y-1 active:scale-95 group">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Reintentar Protocolo de Inicio
        </a>
    </div>

    <div class="absolute bottom-10 left-10 text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">
        Softlinkia Emergency Recovery System v4.5
    </div>
</body>
</html>
