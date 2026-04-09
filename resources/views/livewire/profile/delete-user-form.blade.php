<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-base font-black text-rose-600 tracking-tight uppercase">
            {{ __('Eliminar Cuenta') }}
        </h2>

        <p class="mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">
            {{ __('Una vez que se elimine su cuenta, todos sus recursos y datos se eliminarán de forma permanente. Antes de proceder, descargue cualquier información que desee conservar.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-rose-600 hover:bg-rose-700 px-6 py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all"
    >{{ __('Eliminar Cuenta') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="text-lg font-black text-slate-900 tracking-tight">
                {{ __('¿Está seguro de que desea eliminar su cuenta?') }}
            </h2>

            <p class="mt-1 text-sm text-slate-500 font-bold">
                {{ __('Esta acción es irreversible. Por favor, ingrese su contraseña para confirmar que desea eliminar permanentemente su acceso al sistema de seguridad.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-2xl p-4 font-bold"
                    placeholder="{{ __('Contraseña de Confirmación') }}"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3 font-black uppercase tracking-widest text-[11px]">
                <x-secondary-button x-on:click="$dispatch('close')" class="border-none text-slate-400 hover:text-slate-600">
                    {{ __('Cancelar') }}
                </x-secondary-button>
 
                <x-danger-button class="bg-rose-600 hover:bg-rose-700 px-8 py-3 rounded-xl transition-all shadow-lg shadow-rose-200">
                    {{ __('Eliminar Permanentemente') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
