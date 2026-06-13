<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white dark:bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CUP - Sistema Universitario' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/uagrm-escudo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

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

    <!-- Scripts and Styles -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body x-data class="h-full antialiased font-sans text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-950">
    <div class="min-h-full lg:grid lg:grid-cols-[auto_1fr] lg:grid-rows-[auto_1fr] bg-zinc-50 dark:bg-zinc-950">
        <!-- Sidebar para desktop -->
        <flux:sidebar sticky collapsible="mobile" class="bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800">
            <!-- Brand / Logo -->
            <div class="flex items-center justify-between px-2 py-4 border-b border-zinc-100 dark:border-zinc-800 mb-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/uagrm-escudo.png') }}" class="h-9 w-9 object-contain" alt="UAGRM Escudo">
                    <div class="flex flex-col">
                        <span class="font-bold text-sm tracking-tight leading-none text-zinc-900 dark:text-zinc-100">CUP - ADMISIÓN</span>
                        <span class="text-[10px] text-zinc-400 font-semibold uppercase tracking-wider mt-0.5">Gestión Académica</span>
                    </div>
                </div>
                <!-- Toggle button only visible on mobile inside stashed sidebar -->
                <flux:sidebar.toggle class="lg:hidden text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 p-1 rounded-lg" icon="x-mark" />
            </div>

            <!-- Menú Lateral Dinámico -->
            <livewire:shared.menu-lateral />
        </flux:sidebar>

        <!-- Header para dispositivos móviles -->
        <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between">
            <flux:sidebar.toggle class="text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 p-2 rounded-lg" icon="bars-3" />
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/uagrm-escudo.png') }}" class="h-8 w-8 object-contain" alt="UAGRM Escudo">
                <span class="font-bold text-xs tracking-tight text-zinc-900 dark:text-zinc-100">CUP</span>
            </div>
            <div class="flex items-center gap-3">
                <!-- Dark Mode Toggle Button (Mobile) -->
                <button @click="$store.darkMode.toggle()" type="button" class="relative inline-flex h-9 w-16 items-center justify-between rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 cursor-pointer focus:outline-none select-none transition-colors duration-200" aria-label="Toggle Dark Mode">
                    <!-- Sliding circle indicator -->
                    <span class="absolute left-1 top-1 h-7 w-7 transform rounded-full bg-white dark:bg-zinc-900 shadow-md transition-transform duration-200 ease-in-out border border-zinc-200/50 dark:border-zinc-750"
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
            </div>
        </flux:header>

        <!-- Contenido principal -->
        <flux:main class="p-6 lg:p-10 bg-zinc-50 dark:bg-zinc-950">
            <!-- Breadcrumbs / Top Navbar -->
            <div class="hidden lg:flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-5 mb-8">
                <div class="flex items-center gap-3">
                    <flux:sidebar.toggle class="text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg" icon="bars-3" />
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home">Inicio</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Dashboard Administrativo</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle Button (Desktop) -->
                    <button @click="$store.darkMode.toggle()" type="button" class="relative inline-flex h-9 w-16 items-center justify-between rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 cursor-pointer focus:outline-none select-none transition-colors duration-200" aria-label="Toggle Dark Mode">
                        <!-- Sliding circle indicator -->
                        <span class="absolute left-1 top-1 h-7 w-7 transform rounded-full bg-white dark:bg-zinc-900 shadow-md transition-transform duration-200 ease-in-out border border-zinc-200/50 dark:border-zinc-750"
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
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voiceSearchWidget', (wire) => ({
                isListening: false,
                currentTranscript: '',
                supported: 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window,
                startSpeech() {
                    if (!this.supported) {
                        alert('Su navegador no soporta el reconocimiento de voz. Por favor use Google Chrome, Edge o Safari.');
                        return;
                    }
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    const recognition = new SpeechRecognition();
                    recognition.lang = 'es-BO';
                    recognition.interimResults = true;
                    recognition.maxAlternatives = 1;
                    
                    this.currentTranscript = 'Escuchando...';
                    let finalSpeechResult = '';
                    
                    recognition.onstart = () => {
                        this.isListening = true;
                    };
                    
                    recognition.onresult = (event) => {
                        let transcript = '';
                        for (let i = 0; i < event.results.length; i++) {
                            transcript += event.results[i][0].transcript;
                        }
                        this.currentTranscript = transcript;
                        finalSpeechResult = transcript;
                    };
                    
                    recognition.onerror = (event) => {
                        console.error('Error de voz:', event.error);
                        this.currentTranscript = 'Error de voz: ' + event.error;
                        this.isListening = false;
                    };
                    
                    recognition.onend = () => {
                        this.isListening = false;
                        if (finalSpeechResult.trim()) {
                            this.currentTranscript = `Procesando: "${finalSpeechResult}"`;
                            wire.processVoiceCommand(finalSpeechResult);
                        }
                        setTimeout(() => {
                            if (!this.isListening) {
                                this.currentTranscript = '';
                            }
                        }, 3000);
                    };
                    
                    recognition.start();
                }
            }));
        });
    </script>
    @fluxScripts
</body>
</html>
