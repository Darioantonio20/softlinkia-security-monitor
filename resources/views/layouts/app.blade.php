<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $header ?? config('app.name', 'Softlinkia') }} | Seguridad y Monitoreo</title>
        <meta name="description" content="Plataforma de monitoreo de seguridad Softlinkia. Gestión de dispositivos, incidencias y logs de auditoría en tiempo real.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen bg-[#f8fafc] bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:16px_16px]">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="glass sticky top-0 z-40 border-b border-gray-200/50 backdrop-blur-md">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-600 w-1.5 h-8 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.5)]"></div>
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
