<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Recurso No Localizado | Softlinkia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .liquid-bg {
            background: radial-gradient(circle at 50% 50%, #4f46e5 0%, #0f172a 100%);
        }
    </style>
</head>
<body class="liquid-bg min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-rose-500 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>
    </div>

    <div class="relative z-10 text-center max-w-2xl">
        <p class="text-[14px] font-black text-indigo-400 uppercase tracking-[0.5em] mb-8 animate-bounce">Fallo de Enrutamiento</p>
        <h1 class="text-[150px] font-black text-white leading-none tracking-tighter mb-4 opacity-90">404</h1>
        <div class="h-1.5 w-24 bg-indigo-500 mx-auto rounded-full mb-10 shadow-[0_0_20px_rgba(79,70,229,0.8)]"></div>
        
        <h2 class="text-3xl font-black text-white mb-6">Zona de Navegación No Autorizada</h2>
        <p class="text-indigo-200/70 text-lg font-medium mb-12 leading-relaxed">
            El protocolo de seguridad no ha podido localizar la dirección solicitada. <br>
            Es posible que el recurso haya sido reubicado o el acceso haya expirado.
        </p>

        <a href="/" class="inline-flex items-center px-12 py-5 bg-white text-slate-900 rounded-[2rem] font-black text-xs uppercase tracking-[0.2em] shadow-2xl hover:bg-indigo-50 transition-all hover:-translate-y-1 active:scale-95 group">
            <svg class="w-5 h-5 mr-3 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Regresar al Centro de Control
        </a>
    </div>

    <div class="absolute bottom-10 left-10 text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">
        Softlinkia Adaptive Security Infrastructure v2.1
    </div>
</body>
</html>
