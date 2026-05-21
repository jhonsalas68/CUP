<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-zinc-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CUP - Sistema de Admisión Universitaria') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M12 2L3 6v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6l-9-4z' fill='%234F46E5' fill-opacity='0.15' stroke='%234F46E5' stroke-width='1.5'/%3E%3Cpath d='M12 5L2 9.5L12 14L22 9.5L12 5z' fill='%234F46E5'/%3E%3Cpath d='M12 14.5c2.6 0 3.8-1.3 3.8-1.3v2.7c0 0-1.2-1.4-3.8-1.4s-3.8 1.4-3.8 1.4v-2.7c0 0 1.2 1.3 3.8 1.3z' fill='%23F59E0B'/%3E%3C/svg%3E">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-slate-800 dark:text-zinc-100 bg-slate-50 dark:bg-zinc-950">
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
    </body>
</html>
