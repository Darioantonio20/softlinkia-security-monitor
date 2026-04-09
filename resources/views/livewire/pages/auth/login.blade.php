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

<div class="space-y-10">
    <!-- Header -->
    <div class="text-left">
        <h2 class="text-[32px] font-black text-slate-900 tracking-[-0.04em] leading-tight">Acceso Central</h2>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2">Plataforma de Monitoreo de Seguridad</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-7">
        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Correo Electrónico</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input wire:model="form.email" id="email" type="email" required autofocus 
                    placeholder="usuario@softlinkia.com"
                    class="w-full pl-12 pr-5 py-4 bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all outline-none border">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex justify-between items-center px-1">
                <label for="password" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Contraseña</label>
                @if (Route::has('password.request'))
                    <a class="text-[9px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidó su clave?
                    </a>
                @endif
            </div>
            <div class="relative group" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input wire:model="form.password" id="password" :type="show ? 'text' : 'password'" required 
                    placeholder="••••••••••••"
                    class="w-full pl-12 pr-12 py-4 bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 placeholder-slate-300 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all outline-none border">
                
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m10.89 3.576a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88m-2.037-2.037L4.05 4.05m12.727 12.727L19.5 19.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center px-1">
            <label for="remember" class="flex items-center group cursor-pointer">
                <div class="relative flex items-center justify-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" 
                        class="w-5 h-5 rounded-lg border-slate-200 text-indigo-600 focus:ring-0 transition-all cursor-pointer bg-slate-50">
                </div>
                <span class="ms-3 text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-600 transition-colors">Mantener sesión activa</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="pt-6">
            <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-[0_20px_40px_-10px_rgba(15,23,42,0.3)] hover:shadow-indigo-500/30 transition-all duration-300 active:scale-[0.98]">
                Entrar al Sistema
            </button>
        </div>

        @if (Route::has('register'))
            <div class="text-center pt-8 border-t border-slate-50">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    ¿Nuevo en la plataforma? <a href="{{ route('register') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 ml-1 underline decoration-2 underline-offset-4">Crear Perfil Operador</a>
                </p>
            </div>
        @endif
    </form>
</div>
