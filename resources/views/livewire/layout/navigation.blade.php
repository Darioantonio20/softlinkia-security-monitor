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

<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-100/60 shadow-sm transition-all duration-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="flex justify-between h-20">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center group cursor-pointer">
                    <a href="{{ route('dashboard') }}" wire:navigate class="relative">
                        <div class="absolute -inset-2 bg-indigo-500/10 rounded-xl blur-lg group-hover:bg-indigo-500/20 transition-all duration-500"></div>
                        <x-application-logo class="block h-10 w-auto fill-current text-indigo-600 relative" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate 
                        class="px-5 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 border-none {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-sm shadow-indigo-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('Monitor') }}
                    </x-nav-link>
                    <x-nav-link :href="route('devices')" :active="request()->routeIs('devices')" wire:navigate 
                        class="px-5 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 border-none {{ request()->routeIs('devices') ? 'bg-indigo-50 text-indigo-700 shadow-sm shadow-indigo-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('Dispositivos') }}
                    </x-nav-link>
                    <x-nav-link :href="route('incidents')" :active="request()->routeIs('incidents')" wire:navigate 
                        class="px-5 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 border-none {{ request()->routeIs('incidents') ? 'bg-indigo-50 text-indigo-700 shadow-sm shadow-indigo-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('Incidencias') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center">
                
                {{-- Notificaciones (Campana) --}}
                <div class="relative mr-4 group">
                    <button class="relative p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        
                        {{-- Punto Rojo (Indicador LED) --}}
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border-2 border-white"></span>
                            </span>
                        @endif
                    </button>
                    
                    {{-- Tooltip rápido --}}
                    <div class="absolute top-full right-0 mt-2 hidden group-hover:block bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none">
                        {{ auth()->user()->unreadNotifications->count() }} Alertas nuevas
                    </div>
                </div>

                <div class="h-6 w-[1px] bg-slate-100 mx-6"></div>
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-black rounded-xl text-slate-700 bg-white/50 hover:bg-slate-50 focus:outline-none transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center text-[10px] text-white overflow-hidden shadow-lg group-hover:scale-105 transition-all duration-500">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="text-xs font-black tracking-tight" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                                <svg class="w-2.5 h-2.5 text-slate-400 group-hover:text-slate-900 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- User Context Header --}}
                        <div class="px-6 py-5 bg-slate-50/50 border-b border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Centro de Control</p>
                            <div class="flex flex-col">
                                <span class="text-[14px] font-black text-slate-900 tracking-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[11px] font-bold text-slate-400 truncate">{{ auth()->user()->email }}</span>
                            </div>
                        </div>

                        <div class="p-2 space-y-1">
                            <x-dropdown-link :href="route('profile')" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-xl text-[12px] font-black text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-all group">
                                <div class="p-1.5 bg-slate-100 rounded-lg group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                {{ __('Mi Perfil') }}
                            </x-dropdown-link>

                            <button wire:click="logout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[12px] font-black text-rose-600 hover:bg-rose-50 transition-all group">
                                <div class="p-1.5 bg-rose-50 rounded-lg group-hover:bg-rose-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                </div>
                                {{ __('Cerrar Sesión') }}
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-3 rounded-2xl text-slate-400 hover:text-slate-500 hover:bg-slate-100 transition-all focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-100 overflow-hidden transition-all duration-300">
        <div class="px-6 pt-6 pb-4 space-y-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                {{ __('Monitor General') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('devices')" :active="request()->routeIs('devices')" wire:navigate class="rounded-2xl {{ request()->routeIs('devices') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                {{ __('Dispositivos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('incidents')" :active="request()->routeIs('incidents')" wire:navigate class="rounded-2xl {{ request()->routeIs('incidents') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                {{ __('Incidencias') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-6 pb-8 border-t border-slate-100 bg-slate-50/50 px-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-sm font-black text-white shadow-xl">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="text-base font-black text-slate-900 tracking-tight">{{ auth()->user()->name }}</div>
                    <div class="text-xs font-bold text-slate-500">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="space-y-2">
                <x-responsive-nav-link :href="route('profile')" wire:navigate class="rounded-2xl">
                    {{ __('Mi Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="rounded-2xl text-rose-600">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
