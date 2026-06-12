<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      class="h-full bg-slate-50 dark:bg-zinc-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CUP - Sistema de Admisión Universitaria') }}</title>

        <!-- Sync theme BEFORE anything renders -->
        <script>
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Register Alpine store BEFORE Alpine initializes -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('darkMode', {
                    on: localStorage.getItem('color-theme') === 'dark' ||
                        (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),

                    toggle() {
                        this.on = !this.on;
                        if (this.on) {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('color-theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('color-theme', 'light');
                        }
                    }
                });
            });
        </script>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/uagrm-escudo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body x-data class="h-full font-sans antialiased text-slate-800 dark:text-zinc-100 bg-slate-50 dark:bg-zinc-950">
        <!-- Floating Dark Mode Toggle Button (Guest / Login Screen) -->
        <div class="fixed top-4 right-4 z-50">
            <button @click="$store.darkMode.toggle()" type="button" class="relative inline-flex h-9 w-16 items-center justify-between rounded-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 px-2 cursor-pointer shadow-md focus:outline-none select-none transition-colors duration-200" aria-label="Toggle Dark Mode">
                <!-- Sliding circle indicator -->
                <span class="absolute left-1 top-1 h-7 w-7 transform rounded-full bg-slate-100 dark:bg-zinc-800 shadow-sm transition-transform duration-200 ease-in-out border border-zinc-200/50 dark:border-zinc-700"
                      :class="$store.darkMode.on ? 'translate-x-7' : 'translate-x-0'"></span>

                <!-- Sun Icon (left) -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                     class="z-10 w-4 h-4 transition-colors duration-200"
                     :class="!$store.darkMode.on ? 'text-amber-500' : 'text-zinc-400 dark:text-zinc-500'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>

                <!-- Moon Icon (right) -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                     class="z-10 w-4 h-4 transition-colors duration-200"
                     :class="$store.darkMode.on ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </button>
        </div>

        <div class="min-h-screen grid lg:grid-cols-12">
            <!-- Left Panel: Graphic & Academic Highlights (visible only on desktop) -->
            <div class="hidden lg:flex lg:col-span-5 relative bg-zinc-900 overflow-hidden flex-col justify-between p-12 text-white">
                <!-- Background Glows -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-zinc-900 to-indigo-900/80 -z-10"></div>
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>

                <!-- Header Logo & Branding -->
                <div class="flex items-center gap-3">
                    <x-application-logo class="w-12 h-12" />
                    <div class="flex flex-col">
                        <span class="font-bold text-base tracking-tight text-white leading-none">CUP - ADMISIÓN</span>
                        <span class="text-[10px] text-slate-300 font-semibold uppercase tracking-wider mt-1">Sistema Universitario</span>
                    </div>
                </div>

                <!-- Central Content -->
                <div class="space-y-6 max-w-sm">
                    <h2 class="text-3xl font-black tracking-tight leading-tight">
                        Curso de Admisión y Nivelación Universitaria
                    </h2>
                    <p class="text-sm text-zinc-300 leading-relaxed">
                        Accede de manera rápida y segura para revisar tu postulación, registrar calificaciones docentes o gestionar el proceso de admisión académica de este semestre.
                    </p>
                    <div class="border-t border-zinc-700/50 pt-6 space-y-4">
                        <div class="flex items-center gap-3 text-xs text-zinc-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            <span>Datos encriptados y cifrados</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-zinc-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span>Seguimiento curricular integrado</span>
                        </div>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="text-xs text-zinc-400">
                    &copy; {{ date('Y') }} Comisión de Admisión. Todos los derechos reservados.
                </div>
            </div>

            <!-- Right Panel: The Authentication Form -->
            <div class="lg:col-span-7 flex flex-col justify-center items-center p-6 sm:p-12 md:p-20 relative">
                <!-- Mobile Branding (hidden on large screens) -->
                <div class="lg:hidden flex flex-col items-center gap-2 mb-8">
                    <a href="/" wire:navigate class="flex flex-col items-center gap-2">
                        <x-application-logo class="w-16 h-16" />
                        <h1 class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">CUP - ADMISIÓN</h1>
                        <p class="text-xs text-slate-500 uppercase tracking-widest">Sistema Universitario</p>
                    </a>
                </div>

                <!-- Form Card -->
                <div class="w-full max-w-md bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 shadow-xl shadow-slate-100 dark:shadow-none px-8 py-10 rounded-3xl relative overflow-hidden">
                    <!-- Top subtle accent bar -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>

                    {{ $slot }}
                </div>

                <!-- Bottom Back Link -->
                <div class="mt-8 text-center">
                    <a href="/" wire:navigate class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-indigo-600 dark:text-zinc-500 dark:hover:text-indigo-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver a la Página de Inicio
                    </a>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
