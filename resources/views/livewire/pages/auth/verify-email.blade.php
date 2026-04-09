<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-10">
        <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Verificación</h2>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Valide su identidad de seguridad</p>
    </div>

    <div class="mb-8 text-[13px] font-bold text-slate-500 leading-relaxed">
        {{ __('¡Gracias por registrarse! Antes de comenzar, ¿podría verificar su dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarle? Si no recibió el correo, con gusto le enviaremos otro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] font-black uppercase tracking-widest text-emerald-600">
            {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo proporcionada.') }}
        </div>
    @endif

    <div class="mt-8 flex flex-col gap-4">
        <x-primary-button wire:click="sendVerification" class="w-full justify-center py-4">
            {{ __('Re-enviar Correo de Verificación') }}
        </x-primary-button>

        <button wire:click="logout" type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-600 transition-colors">
            {{ __('Cerrar Sesión') }}
        </button>
    </div>
</div>
