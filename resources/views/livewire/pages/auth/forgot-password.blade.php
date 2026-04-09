<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-10">
        <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Recuperación</h2>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Restablezca su acceso de seguridad</p>
    </div>

    <div class="mb-8 text-[13px] font-bold text-slate-500 leading-relaxed">
        {{ __('¿Olvidó su contraseña? No hay problema. Indíquenos su dirección de correo electrónico y le enviaremos un enlace de restablecimiento que le permitirá elegir una nueva.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2" />
            <x-text-input wire:model="email" id="email" class="block w-full bg-slate-50 p-4 rounded-2xl border-slate-200" type="email" name="email" required autofocus placeholder="ejemplo@softlinkia.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Enviar Enlace de Recuperación') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('login') }}" wire:navigate class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 transition-colors">
                Volver al inicio de sesión
            </a>
        </div>
    </form>
</div>
