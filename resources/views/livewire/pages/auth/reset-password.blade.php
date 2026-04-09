<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', Rules\Password::defaults(), 'confirmed'],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-10">
        <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Nueva Clave</h2>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Establezca su nueva contraseña de acceso</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2" />
            <x-text-input wire:model="email" id="email" class="block w-full bg-slate-50 p-4 rounded-2xl border-slate-200" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Nueva Contraseña')" class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2" />
            <div class="relative group" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <x-text-input wire:model="password" id="password" :type="show ? 'text' : 'password'" class="block w-full bg-slate-50 p-4 rounded-2xl border-slate-200 pr-12" required autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none">
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m10.89 3.576a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88m-2.037-2.037L4.05 4.05m12.727 12.727L19.5 19.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-2" />
            <div class="relative group" x-data="{ showConf: false }">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <x-text-input wire:model="password_confirmation" id="password_confirmation" :type="showConf ? 'text' : 'password'" class="block w-full bg-slate-50 p-4 rounded-2xl border-slate-200 pr-12"
                              required autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="showConf = !showConf" class="absolute inset-y-0 right-0 pr-5 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none">
                    <svg x-show="!showConf" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showConf" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m10.89 3.576a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88m-2.037-2.037L4.05 4.05m12.727 12.727L19.5 19.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Restablecer Contraseña') }}
            </x-primary-button>
        </div>
    </form>
</div>
