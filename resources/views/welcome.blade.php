<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-zinc-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>CUP - Sistema de Admisión Universitaria</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M12 2L3 6v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6l-9-4z' fill='%234F46E5' fill-opacity='0.15' stroke='%234F46E5' stroke-width='1.5'/%3E%3Cpath d='M12 5L2 9.5L12 14L22 9.5L12 5z' fill='%234F46E5'/%3E%3Cpath d='M12 14.5c2.6 0 3.8-1.3 3.8-1.3v2.7c0 0-1.2-1.4-3.8-1.4s-3.8 1.4-3.8 1.4v-2.7c0 0 1.2 1.3 3.8 1.3z' fill='%23F59E0B'/%3E%3C/svg%3E">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Theme Sync Script -->
        <script>
            const isDark = localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            document.addEventListener('DOMContentLoaded', () => {
                const toggleBtn = document.getElementById('dark-mode-toggle');
                const indicator = document.getElementById('sliding-indicator');
                const sunIcon = document.getElementById('sun-icon-widget');
                const moonIcon = document.getElementById('moon-icon-widget');

                if (toggleBtn && indicator && sunIcon && moonIcon) {
                    function updateWidget() {
                        if (document.documentElement.classList.contains('dark')) {
                            indicator.classList.remove('translate-x-0');
                            indicator.classList.add('translate-x-7');
                            sunIcon.setAttribute('class', 'z-10 w-4 h-4 transition-colors duration-200 text-zinc-400 dark:text-zinc-500');
                            moonIcon.setAttribute('class', 'z-10 w-4 h-4 transition-colors duration-200 text-indigo-650 dark:text-indigo-400');
                        } else {
                            indicator.classList.remove('translate-x-7');
                            indicator.classList.add('translate-x-0');
                            sunIcon.setAttribute('class', 'z-10 w-4 h-4 transition-colors duration-200 text-amber-500');
                            moonIcon.setAttribute('class', 'z-10 w-4 h-4 transition-colors duration-200 text-zinc-400 dark:text-zinc-500');
                        }
                    }

                    updateWidget();

                    toggleBtn.addEventListener('click', () => {
                        if (document.documentElement.classList.contains('dark')) {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('color-theme', 'light');
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('color-theme', 'dark');
                        }
                        updateWidget();
                    });
                }
            });
        </script>
    </head>
    <body class="h-full antialiased font-sans text-slate-800 dark:text-zinc-100 bg-slate-50 dark:bg-zinc-950 flex flex-col justify-between">
        <div class="relative min-h-screen flex flex-col justify-between">
            <!-- Background Decorative Gradients -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 -left-40 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl"></div>
            </div>

            <!-- Top Header / Navigation -->
            <header class="max-w-7xl w-full mx-auto px-6 py-6 flex justify-between items-center border-b border-slate-200/60 dark:border-zinc-800/50">
                <div class="flex items-center gap-3">
                    <x-application-logo class="w-10 h-10 text-indigo-600 dark:text-indigo-500" />
                    <div class="flex flex-col">
                        <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white leading-none">CUP - ADMISIÓN</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Sistema Universitario</span>
                    </div>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        <!-- Dark Mode Toggle Button -->
                        <button id="dark-mode-toggle" type="button" class="relative inline-flex h-9 w-16 items-center justify-between rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 cursor-pointer focus:outline-none select-none transition-colors duration-200" aria-label="Toggle Dark Mode">
                            <!-- Sliding circle indicator -->
                            <span id="sliding-indicator" class="absolute left-1 top-1 h-7 w-7 transform rounded-full bg-white dark:bg-zinc-900 shadow-md transition-transform duration-200 ease-in-out border border-zinc-200/50 dark:border-zinc-750 translate-x-0"></span>

                            <!-- Sun Icon (left) -->
                            <svg id="sun-icon-widget" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="z-10 w-4 h-4 transition-colors duration-200">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>

                            <!-- Moon Icon (right) -->
                            <svg id="moon-icon-widget" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="z-10 w-4 h-4 transition-colors duration-200">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>

                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                Ir al Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white">
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition duration-150 shadow-sm">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </header>

            <!-- Main Hero and Features -->
            <main class="max-w-7xl w-full mx-auto px-6 py-12 lg:py-24 flex flex-col lg:flex-row items-center gap-12 flex-1">
                <!-- Left Side: Hero Info -->
                <div class="flex-1 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Admisiones Abiertas - Gestión I-2026
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                        Tu Futuro Universitario <br/>
                        <span class="bg-gradient-to-r from-indigo-600 to-indigo-500 bg-clip-text text-transparent">Empieza Aquí</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-500 dark:text-zinc-400 max-w-xl mx-auto lg:mx-0">
                        Bienvenido al portal oficial del Curso de Admisión y Nivelación Universitaria (CUP). Regístrate, consulta tus grupos de clases, revisa tus exámenes y sigue tu estado de admisión en tiempo real.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 pt-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-center shadow-md shadow-indigo-600/10 hover:shadow-lg transition-all duration-200">
                                Entrar a mi Panel de Control
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-center shadow-md shadow-indigo-600/10 hover:shadow-lg transition-all duration-200">
                                Acceder al Portal Administrativo
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 font-semibold rounded-xl text-center transition-colors">
                                    Registro de Postulantes
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Right Side: Graphic Steps / Features Card -->
                <div class="flex-1 w-full max-w-md">
                    <div class="bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-xl shadow-slate-100 dark:shadow-none space-y-6">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Etapas del Proceso de Admisión</h3>
                        
                        <!-- Step 1 -->
                        <div class="flex gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                1
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Inscripción y Registro</h4>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 leading-normal">Carga tus datos y selecciona tu carrera preferida junto a una opción secundaria.</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                2
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Asignación de Grupos y Materias</h4>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 leading-normal">El sistema te asignará a un grupo, horario y docente calificado de forma automática.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                3
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Evaluación Continua</h4>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 leading-normal">Planilla académica de tres exámenes parciales bajo ponderaciones académicas estándar (30/30/40).</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                4
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white">Admisión y Ranking de Cupos</h4>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 leading-normal">Selección automática de ingresantes basada en mérito académico y asignación flexible de segunda opción.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Academic Footer -->
            <footer class="py-8 border-t border-slate-200/60 dark:border-zinc-800/50 text-center text-xs text-slate-400 dark:text-zinc-600 max-w-7xl w-full mx-auto px-6">
                &copy; {{ date('Y') }} Comisión de Admisión Universitaria. Todos los derechos reservados.
            </footer>
        </div>
    </body>
</html>
