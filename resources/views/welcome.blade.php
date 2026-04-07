<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Softlinkia | Bienvenido</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
            <h1 class="text-5xl font-bold text-gray-800 mb-4">¡Hola Mundo!</h1>
            <p class="text-gray-600 font-medium tracking-wide uppercase">Softlinkia Security Monitor - v1.0</p>
            
            @if (Route::has('login'))
                <div class="mt-8 flex gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-blue-600 underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-blue-600 underline">Log in</a>
                        <a href="{{ route('register') }}" class="text-blue-600 underline ml-4">Register</a>
                    @endauth
                </div>
            @endif
        </div>
    </body>
</html>
