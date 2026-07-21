<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header & Title -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/30 text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-2 backdrop-blur-sm border border-indigo-400/20">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Simulador Interactivo de Admisión
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Calculadora e Indicador de Admisión</h1>
                <p class="text-indigo-200 text-sm mt-1 max-w-2xl">
                    Mueve los deslizadores o cambia cualquier nota para simular diferentes escenarios ("¿Qué pasa si saco 30 ó 90?") y saber si apruebas en tiempo real.
                </p>
            </div>

            <!-- Indicador Promedio Proyectado Gigante -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 sm:p-5 text-center min-w-[200px] shadow-lg">
                <div class="text-xs uppercase tracking-widest text-indigo-200 font-semibold mb-1">Promedio Proyectado</div>
                <div class="text-4xl sm:text-5xl font-black text-amber-400 tracking-tight transition-all duration-300">
                    {{ number_format($promedioProyectado, 2) }}
                </div>
                <div class="mt-2">
                    @if($promedioProyectado >= 60)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            PROYECTADO APROBADO
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-400/30">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                            REQUERIDO SUBIR NOTA
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Career & Postulante Filter Toolbar -->
    <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Filtros de Búsqueda y Selección</h2>
            </div>

            <button wire:click="restablecerNotasOficiales" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold rounded-lg transition flex items-center gap-1.5 border border-zinc-200 dark:border-zinc-700">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Restablecer Notas Oficiales
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Filtrar por Carrera -->
            <div>
                <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">1. Filtrar por Carrera</label>
                <select wire:model.live="selectedCarreraId" class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 font-semibold">
                    <option value="">Todas las Carreras</option>
                    @foreach($carrerasLista as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->sigla }})</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Buscar Postulante (Nombre o CI) -->
            <div>
                <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">2. Buscar por Nombre o CI</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.200ms="searchPostulante" class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-xl pl-9 pr-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Ej: Carlos o 1234567...">
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- 3. Seleccionar Postulante de la lista -->
            <div>
                <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">3. Seleccionar Alumno ({{ count($postulantesLista) }})</label>
                <select wire:model.live="selectedPostulanteId" class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 font-semibold">
                    @if(empty($postulantesLista))
                        <option value="">No hay postulantes coincidentes</option>
                    @else
                        @foreach($postulantesLista as $p)
                            <option value="{{ $p['id'] }}">{{ $p['nombres_apellidos'] }} (CI: {{ $p['ci'] }}) - {{ $p['carrera'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    @if(!$postulante)
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 p-8 rounded-2xl text-center space-y-2">
            <svg class="w-10 h-10 mx-auto text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <p class="font-bold text-base">No se encontró ningún postulante que coincida con los filtros seleccionados.</p>
            <p class="text-xs text-amber-600 dark:text-amber-400">Intenta borrar la búsqueda o cambiar la carrera en los filtros superiores.</p>
        </div>
    @else
        <!-- Information Grid: Student & Career Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Student Profile Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow">
                    {{ strtoupper(substr($postulante->nombres_apellidos ?? 'P', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Postulante Seleccionado</div>
                    <div class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $postulante->nombres_apellidos }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">CI: {{ $postulante->ci }} | ID: #{{ $postulante->id }}</div>
                </div>
            </div>

            <!-- Primera Opción Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Primera Opción de Carrera</div>
                <div class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">
                    {{ $carreraPrimera?->nombre ?? 'Sin Asignar' }}
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Cupos 1ª Opción: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $carreraPrimera?->cupos_primera_opcion ?? '0' }} plazas</span>
                </div>
            </div>

            <!-- Segunda Opción Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-1">Segunda Opción de Carrera</div>
                <div class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">
                    {{ $carreraSegunda?->nombre ?? 'Sin Asignar' }}
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Cupos 2ª Opción: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $carreraSegunda?->cupos_segunda_opcion ?? '0' }} plazas</span>
                </div>
            </div>
        </div>

        <!-- Target Calculation Tool & Slider Section -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-6">

            <!-- Dynamic Header for Target Calculator -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Calculador de Nota Objetivo Automático
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Ingresa la nota deseada (Ej: 60 ó 80) y calcularemos qué notas requiere en los exámenes.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-40">
                        <input type="number" min="0" max="100" step="1" wire:model.defer="targetScore" class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-xl px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-indigo-500" placeholder="Ej: 60">
                        <span class="absolute right-3 top-2.5 text-xs text-zinc-400 font-semibold">Pts</span>
                    </div>

                    <button wire:click="calcularObjetivo" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-sm hover:shadow flex items-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Simular Objetivo
                    </button>
                </div>
            </div>

            <!-- Materias & Exam Interactive Sliders -->
            @if(empty($materiasData))
                <div class="text-center py-10 text-zinc-400 dark:text-zinc-500 text-sm">
                    No existen materias registradas para la carrera del postulante en la gestión activa.
                </div>
            @else
                <div class="space-y-6">
                    @foreach($materiasData as $mIndex => $materia)
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 rounded-xl p-5 border border-zinc-200/80 dark:border-zinc-800">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                                        {{ $materia['sigla'] }}
                                    </span>
                                    <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-base">
                                        {{ $materia['materia_nombre'] }}
                                    </h3>
                                </div>
                            </div>

                            <!-- List of Exams with Fully Editable Sliders & Inputs -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($materia['examenes'] as $eIndex => $exam)
                                    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $exam['nombre'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                                {{ $exam['ponderacion'] }}% ponderado
                                            </span>
                                        </div>

                                        @if($exam['es_real'])
                                            <div class="flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Nota Registrada Oficial:
                                                </span>
                                                <span class="font-bold">{{ number_format($exam['nota_real'], 2) }} pts</span>
                                            </div>
                                        @endif

                                        <!-- Fully Interactive Slider & Numeric Input -->
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Simular Nota:</span>
                                                <div class="flex items-center gap-1">
                                                    <input type="number" min="0" max="100" step="1"
                                                           wire:model.live.debounce.150ms="materiasData.{{ $mIndex }}.examenes.{{ $eIndex }}.nota_simulada"
                                                           class="w-20 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-lg px-2 py-1 text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500">
                                                    <span class="text-xs text-zinc-400 font-bold">pts</span>
                                                </div>
                                            </div>

                                            <input type="range" min="0" max="100" step="1"
                                                   wire:model.live="materiasData.{{ $mIndex }}.examenes.{{ $eIndex }}.nota_simulada"
                                                   class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">

                                            <div class="flex justify-between text-[10px] text-zinc-400 font-mono">
                                                <span>0 pts</span>
                                                <span>50 pts</span>
                                                <span>100 pts</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
