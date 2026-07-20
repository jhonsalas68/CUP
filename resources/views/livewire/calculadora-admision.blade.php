<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header & Title -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/30 text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-2 backdrop-blur-sm border border-indigo-400/20">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Herramienta Interactiva CUP
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Calculadora e Indicador de Admisión</h1>
                <p class="text-indigo-200 text-sm mt-1 max-w-2xl">
                    Simula tus notas de exámenes restantes en tiempo real, proyecta tu promedio final ponderado y calcula la nota exacta necesaria para asegurar tu admisión.
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

    <!-- Admin/Docente Postulante Selector -->
    @if(auth()->user() && !auth()->user()->hasRole('Postulante') && count($postulantesLista) > 0)
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-950/50 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Modo Administrador / Docente</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Selecciona un postulante para consultar o simular sus notas:</div>
                </div>
            </div>
            <select wire:model.live="selectedPostulanteId" class="w-full sm:w-72 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 font-medium">
                @foreach($postulantesLista as $p)
                    <option value="{{ $p->id }}">{{ $p->nombres_apellidos }} (CI: {{ $p->ci }})</option>
                @endforeach
            </select>
        </div>
    @endif

    @if(!$postulante)
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 p-6 rounded-xl text-center">
            <p class="font-medium">No se encontró información del postulante registrado.</p>
        </div>
    @else
        <!-- Information Grid: Student & Career Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Student Profile Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow">
                    {{ strtoupper(substr($postulante->nombres_apellidos ?? 'P', 0, 1)) }}
                </div>
                <div>
                    <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Postulante</div>
                    <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $postulante->nombres_apellidos }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">CI: {{ $postulante->ci }} | Cód: #{{ $postulante->id }}</div>
                </div>
            </div>

            <!-- Primera Opción Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Primera Opción de Carrera</div>
                <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $carreraPrimera?->nombre ?? 'Sin Asignar' }}
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Cupos: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $carreraPrimera?->cupos_primera_opcion ?? '0' }} disponibles</span>
                </div>
            </div>

            <!-- Segunda Opción Card -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-1">Segunda Opción de Carrera</div>
                <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $carreraSegunda?->nombre ?? 'Sin Asignar' }}
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    Cupos: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $carreraSegunda?->cupos_segunda_opcion ?? '0' }} disponibles</span>
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
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Ingresa la nota final que deseas obtener y calcularemos automáticamente qué nota necesitas sacar en los exámenes pendientes.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-40">
                        <input type="number" min="0" max="100" step="1" wire:model.defer="targetScore" class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 rounded-xl px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-indigo-500" placeholder="Ej: 60">
                        <span class="absolute right-3 top-2.5 text-xs text-zinc-400 font-semibold">Pts</span>
                    </div>

                    <button wire:click="calcularObjetivo" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-sm hover:shadow flex items-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Calcular Requerido
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

                            <!-- List of Exams with Sliders -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($materia['examenes'] as $eIndex => $exam)
                                    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $exam['nombre'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                                {{ $exam['ponderacion'] }}% ponderado
                                            </span>
                                        </div>

                                        <!-- Badge: Real vs Simulated -->
                                        @if($exam['es_real'])
                                            <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/40 p-2.5 rounded-lg border border-emerald-200 dark:border-emerald-900">
                                                <span class="text-xs font-medium text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Nota Registrada Oficial
                                                </span>
                                                <span class="text-base font-black text-emerald-700 dark:text-emerald-300">
                                                    {{ number_format($exam['nota_real'], 2) }} pts
                                                </span>
                                            </div>
                                        @else
                                            <!-- Interactive Slider -->
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Nota Simulada
                                                    </span>
                                                    <span class="font-extrabold text-zinc-900 dark:text-zinc-100 text-sm">
                                                        {{ number_format($exam['nota_simulada'], 2) }} pts
                                                    </span>
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
                                        @endif
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
