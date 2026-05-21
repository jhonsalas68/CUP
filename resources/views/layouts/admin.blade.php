<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white dark:bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CUP - Sistema Universitario' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M12 2L3 6v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6l-9-4z' fill='%234F46E5' fill-opacity='0.15' stroke='%234F46E5' stroke-width='1.5'/%3E%3Cpath d='M12 5L2 9.5L12 14L22 9.5L12 5z' fill='%234F46E5'/%3E%3Cpath d='M12 14.5c2.6 0 3.8-1.3 3.8-1.3v2.7c0 0-1.2-1.4-3.8-1.4s-3.8 1.4-3.8 1.4v-2.7c0 0 1.2 1.3 3.8 1.3z' fill='%23F59E0B'/%3E%3C/svg%3E">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-950">
    <div class="min-h-full lg:grid lg:grid-cols-[auto_1fr] lg:grid-rows-[auto_1fr]">
        <!-- Sidebar para desktop -->
        <flux:sidebar sticky collapsible class="bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 w-64">
            <!-- Brand / Logo -->
            <div class="flex items-center gap-3 px-2 py-4 border-b border-zinc-100 dark:border-zinc-800 mb-6">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-500/20">
                    U
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm tracking-tight leading-none text-zinc-900 dark:text-zinc-100">CUP - ADMISIÓN</span>
                    <span class="text-[10px] text-zinc-400 font-semibold uppercase tracking-wider mt-0.5">Gestión Académica</span>
                </div>
            </div>

            <!-- Menú Lateral Dinámico -->
            <livewire:shared.menu-lateral />
        </flux:sidebar>

        <!-- Header para dispositivos móviles -->
        <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between">
            <flux:sidebar.toggle class="text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 p-2 rounded-lg" icon="bars-3" />
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-sm">
                    U
                </div>
                <span class="font-bold text-xs tracking-tight text-zinc-900 dark:text-zinc-100">CUP</span>
            </div>
            <flux:dropdown>
                <button class="flex items-center">
                    <flux:avatar class="w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-700" name="{{ auth()->user()->name }}" />
                </button>
                <flux:menu class="w-48">
                    <flux:menu.item href="{{ route('profile') }}" icon="user">Mi Perfil</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="$dispatch('logout')" icon="arrow-right-start-on-rectangle" class="text-rose-600 dark:text-rose-400">Cerrar Sesión</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Contenido principal -->
        <flux:main class="p-6 lg:p-10">
            <!-- Breadcrumbs / Top Navbar -->
            <div class="hidden lg:flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-5 mb-8">
                <div>
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home">Inicio</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Dashboard Administrativo</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Badge de Rol -->
                    <div class="text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 px-3.5 py-1.5 rounded-full border border-indigo-100 dark:border-indigo-900/50 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        Rol: {{ auth()->user()->roles->pluck('name')->first() ?? 'Usuario' }}
                    </div>

                    <!-- Usuario Dropdown -->
                    <flux:dropdown position="bottom" align="end">
                        <button class="flex items-center gap-2 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 p-1.5 rounded-xl transition-all duration-200">
                            <flux:avatar class="w-8 h-8 rounded-xl border border-zinc-200 dark:border-zinc-700" name="{{ auth()->user()->name }}" />
                            <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <flux:menu class="w-48">
                            <flux:menu.item href="{{ route('profile') }}" icon="user">Mi Perfil</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item onclick="document.getElementById('logout-btn-sidebar').click()" icon="arrow-right-start-on-rectangle" class="text-rose-600 dark:text-rose-400">Cerrar Sesión</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>

            <!-- Alertas / Toast -->
            <div class="mb-6">
                @if (session()->has('message'))
                    <div class="p-4 mb-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2 animate-fade-in">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" />
                        </svg>
                        {{ session('message') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="p-4 mb-4 text-sm text-rose-700 bg-rose-50 dark:bg-rose-950/30 dark:text-rose-400 rounded-2xl border border-rose-100 dark:border-rose-900/50 flex items-center gap-2 animate-fade-in">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <!-- Slot de Contenido -->
            <div class="space-y-6">
                {{ $slot }}
            </div>
        </flux:main>
    </div>

    <!-- Toast Component -->
    <flux:toast />
</body>
</html>
