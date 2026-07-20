<div x-data="{
        isOpen: @entangle('isOpen'),
        selectedIndex: 0,
        init() {
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    this.isOpen = !this.isOpen;
                } else if (e.key === 'Escape' && this.isOpen) {
                    this.isOpen = false;
                }
            });
        }
     }"
     @open-command-palette.window="isOpen = true"
     x-cloak>

    <!-- Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isOpen = false"
         class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm z-50 transition-opacity"></div>

    <!-- Modal Container -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 md:p-20 overflow-y-auto pointer-events-none">

        <div class="w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden pointer-events-auto flex flex-col my-auto sm:my-0">

            <!-- Search Input Header -->
            <div class="relative flex items-center px-4 border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <svg class="w-5 h-5 text-indigo-500 shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <input type="text"
                       wire:model.live.debounce.150ms="search"
                       x-ref="searchInput"
                       x-effect="if (isOpen) $nextTick(() => $refs.searchInput.focus())"
                       placeholder="Escribe para buscar postulantes, carreras, grupos o navegación... (Ctrl + K)"
                       class="w-full py-4 bg-transparent text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none border-none ring-0">

                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <kbd class="px-2 py-1 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md">ESC</kbd>
                </div>
            </div>

            <!-- Search Results Content -->
            <div class="max-h-96 overflow-y-auto p-3 space-y-4 divide-y divide-zinc-100 dark:divide-zinc-800/60">

                <!-- Section 1: Navigation -->
                @if(count($results['navigation']) > 0)
                    <div class="space-y-1 pt-1">
                        <div class="px-3 text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                            Rutas y Módulos del Sistema
                        </div>
                        @foreach($results['navigation'] as $item)
                            <a href="{{ $item['url'] }}" @click="isOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-zinc-700 dark:text-zinc-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition group">
                                <div class="p-2 bg-zinc-100 dark:bg-zinc-800 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 rounded-lg text-zinc-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </div>
                                <div class="flex flex-col min-w-0 flex-1">
                                    <span class="text-xs font-bold truncate">{{ $item['title'] }}</span>
                                    <span class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate">{{ $item['description'] }}</span>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-400 group-hover:text-indigo-500">Ir</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Section 2: Postulantes -->
                @if(count($results['postulantes']) > 0)
                    <div class="space-y-1 pt-3">
                        <div class="px-3 text-[11px] font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-wider flex items-center justify-between">
                            <span>Postulantes Coincidentes</span>
                            <span class="text-[10px] font-normal text-zinc-400">{{ count($results['postulantes']) }} encontrados</span>
                        </div>
                        @foreach($results['postulantes'] as $p)
                            <a href="{{ route('calculadora', ['postulanteId' => $p->id]) }}" @click="isOpen = false" class="flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-zinc-700 dark:text-zinc-300 transition group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($p->nombres_apellidos, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold truncate text-zinc-900 dark:text-zinc-100">{{ $p->nombres_apellidos }}</span>
                                        <span class="text-[11px] text-zinc-400">CI: {{ $p->ci }} | Carrera: {{ $p->carreraPrimeraOpn?->nombre ?? 'Sin asignar' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-900">
                                        {{ number_format($p->nota_final ?? 0, 2) }} pts
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Section 3: Carreras -->
                @if(count($results['carreras']) > 0)
                    <div class="space-y-1 pt-3">
                        <div class="px-3 text-[11px] font-bold text-purple-600 dark:text-purple-500 uppercase tracking-wider">
                            Carreras Universitarias
                        </div>
                        @foreach($results['carreras'] as $c)
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $c->nombre }}</span>
                                    <span class="text-[11px] text-zinc-400">Código: {{ $c->codigo ?? 'N/A' }}</span>
                                </div>
                                <div class="text-[11px] font-medium text-zinc-500">
                                    Cupos 1ª: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $c->cupos_primera_opcion }}</span> | 
                                    2ª: <span class="font-bold text-purple-600 dark:text-purple-400">{{ $c->cupos_segunda_opcion }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Section 4: Grupos -->
                @if(count($results['grupos']) > 0)
                    <div class="space-y-1 pt-3">
                        <div class="px-3 text-[11px] font-bold text-sky-600 dark:text-sky-500 uppercase tracking-wider">
                            Grupos Académicos
                        </div>
                        @foreach($results['grupos'] as $g)
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Grupo {{ $g->nombre }}</span>
                                    <span class="text-[11px] text-zinc-400">Materia: {{ $g->materia?->nombre ?? 'N/A' }}</span>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 rounded">
                                    Cupo: {{ $g->cupo_maximo }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- No Results State -->
                @if(count($results['navigation']) === 0 && count($results['postulantes']) === 0 && count($results['carreras']) === 0 && count($results['grupos']) === 0)
                    <div class="text-center py-8 text-zinc-400 space-y-2">
                        <svg class="w-8 h-8 mx-auto text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs font-medium">No se encontraron coincidencias para "<span class="text-zinc-700 dark:text-zinc-200 font-bold">{{ $search }}</span>"</p>
                    </div>
                @endif

            </div>

            <!-- Modal Footer Keyboard Guide -->
            <div class="px-4 py-2.5 bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-zinc-400">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 rounded text-[10px] shadow-xs">Ctrl + K</kbd> Abrir / Cerrar
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 rounded text-[10px] shadow-xs">ESC</kbd> Salir
                    </span>
                </div>
                <span class="font-medium text-indigo-500 dark:text-indigo-400">CUP Buscador Inteligente</span>
            </div>

        </div>
    </div>
</div>
