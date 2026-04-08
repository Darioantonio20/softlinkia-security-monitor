<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-10">
        <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Acceso</h2>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Ingrese sus credenciales de seguridad</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico" />
            <x-text-input wire:model="form.email" id="email" class="block w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="ejemplo@softlinkia.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input wire:model="form.password" id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center group cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded-lg border-gray-200 text-indigo-600 shadow-sm focus:ring-0 transition-all cursor-pointer w-5 h-5" name="remember">
                <span class="ms-3 text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-gray-600 transition-colors">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                    ¿Olvidó su clave?
                </a>
            @endif
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full justify-center">
                Iniciar Sesión
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="text-center text-[10px] font-black uppercase tracking-widest text-gray-400 mt-8">
                ¿No tiene cuenta? <a href="{{ route('register') }}" wire:navigate class="text-indigo-600 hover:text-indigo-700 ml-1">Regístrese</a>
            </p>
        @endif
    </form>
</div>
