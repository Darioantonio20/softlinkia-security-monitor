<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="glass sticky top-0 z-[60] border-b border-white/40 backdrop-blur-2xl shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center group">
                    <a href="{{ route('dashboard') }}" wire:navigate class="transition-transform duration-500 group-hover:scale-105">
                        <x-application-logo class="block h-10 w-auto fill-current text-slate-900" />
                    </a>
                    <div class="ml-4 h-6 w-[1px] bg-slate-200 hidden sm:block"></div>
                    <span class="ml-4 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 hidden lg:block">Security Monitor</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('devices')" :active="request()->routeIs('devices')" wire:navigate>
                        {{ __('Dispositivos') }}
                    </x-nav-link>
                    <x-nav-link :href="route('incidents')" :active="request()->routeIs('incidents')" wire:navigate>
                        {{ __('Incidencias') }}
                    </x-nav-link>
                    @role('Administrador')
                    <x-nav-link :href="route('audit-logs')" :active="request()->routeIs('audit-logs')" wire:navigate>
                        {{ __('Bitácora') }}
                    </x-nav-link>
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-5 py-2.5 glass border-white/40 text-xs font-black uppercase tracking-widest text-slate-600 hover:text-slate-900 hover:bg-white/60 focus:outline-none transition duration-300 rounded-xl shadow-sm group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center text-[10px] text-white overflow-hidden shadow-lg group-hover:scale-110 transition-transform">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                            </div>

                            <div class="ms-3 opacity-50 group-hover:opacity-100 transition-opacity">
                                <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-slate-50 bg-slate-50/50">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sesión Iniciada como</p>
                            <p class="text-xs font-bold text-slate-800 truncate mt-1">{{ auth()->user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile')" wire:navigate class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link class="py-3 text-[10px] font-black uppercase tracking-widest text-rose-600 hover:bg-rose-50 transition-colors">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-3 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-white/50 focus:outline-none transition duration-300">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden glass border-t border-white/20">
        <div class="pt-4 pb-6 space-y-2 px-4 text-left">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('devices')" :active="request()->routeIs('devices')" wire:navigate>
                {{ __('Dispositivos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('incidents')" :active="request()->routeIs('incidents')" wire:navigate>
                {{ __('Incidencias') }}
            </x-responsive-nav-link>
            @role('Administrador')
            <x-responsive-nav-link :href="route('audit-logs')" :active="request()->routeIs('audit-logs')" wire:navigate>
                {{ __('Bitácora') }}
            </x-responsive-nav-link>
            @endrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-6 pb-8 border-t border-white/20 bg-white/10 px-6">
            <div class="flex items-center gap-4 mb-6 text-left">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-sm font-black text-white shadow-xl">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="text-base font-black text-slate-900 font-outfit" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="text-xs font-bold text-slate-500">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="space-y-3 text-left">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Mi Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-left">
                    <x-responsive-nav-link class="text-rose-600">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
