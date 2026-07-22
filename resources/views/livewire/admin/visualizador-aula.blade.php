<div class="container mx-auto p-6 space-y-6">
    <!-- Header/Breadcrumbs -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                <a href="{{ route('admin.aulas') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Aulas</a>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Croquis de Asientos</span>
            </div>
            <h1 class="text-2xl font-black text-zinc-900 dark:text-white mt-1">
                Visualizador de Aula: <span class="text-violet-600 dark:text-violet-400">{{ $aula->nombre }}</span>
            </h1>
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                Capacidad: {{ $aula->capacidad }} asientos | Ubicación: {{ $aula->ubicacion ?: 'No especificada' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.aulas') }}" class="inline-flex items-center justify-center px-4 py-2 border border-zinc-200 dark:border-zinc-800 text-sm font-semibold rounded-xl text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition duration-150 cursor-pointer">
                Regresar a Lista
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/35 rounded-xl text-emerald-800 dark:text-emerald-350 text-xs font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-emerald-600 dark:text-emerald-450">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left: Configuration and Unassigned Students -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Grupo Selector -->
            <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-xs space-y-3.5">
                <h3 class="text-xs font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider">Grupo Académico</h3>
                <div class="relative">
                    <select wire:model.live="selectedGrupoId" class="w-full pl-3 pr-10 py-2.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-750 text-zinc-800 dark:text-zinc-100 rounded-xl text-xs font-bold focus:ring-2 focus:ring-violet-500 focus:border-violet-500 focus:outline-hidden transition duration-150">
                        @if($grupos->isEmpty())
                            <option value="">Sin grupos asignados</option>
                        @else
                            @foreach($grupos as $g)
                                <option value="{{ $g->id }}">{{ $g->materia?->nombre }} - {{ $g->nombre }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Seating Actions -->
            @if($grupo)
                <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-xs space-y-3">
                    <h3 class="text-xs font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider mb-2">Acciones</h3>
                    
                    <!-- Criterio Dropdown -->
                    <div class="space-y-1 mb-2">
                        <label class="text-[10px] font-bold text-zinc-400 dark:text-zinc-550 uppercase tracking-wider block">Criterio de Orden</label>
                        <select wire:model="distributionCriteria" class="w-full pl-3 pr-8 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-violet-500 focus:outline-hidden transition duration-150 cursor-pointer">
                            <option value="alfabetico_asc">Alfabético: A-Z</option>
                            <option value="alfabetico_desc">Alfabético: Z-A</option>
                            <option value="nota_desc">Calificación: Mayor a Menor</option>
                            <option value="nota_asc">Calificación: Menor a Mayor</option>
                            <option value="aleatorio">Aleatorio / Al azar</option>
                        </select>
                    </div>
                                   <button wire:click="autoAssignRemaining" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-violet-600 hover:bg-violet-750 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                        <!-- Loading Spinner -->
                        <svg wire:loading wire:target="autoAssignRemaining" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Default Icon -->
                        <svg wire:loading.remove wire:target="autoAssignRemaining" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7C4.68 9.547 4.634 10.768 4.634 12c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.092-1.209.138-2.43.138-3.662z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10.5l3 3 3-3" />
                        </svg>
                        <span wire:loading wire:target="autoAssignRemaining">Procesando...</span>
                        <span wire:loading.remove wire:target="autoAssignRemaining">Auto-Distribuir</span>
                    </button>
 
                    <button wire:click="clearAllAssignments" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-750 disabled:opacity-50 disabled:cursor-not-allowed text-zinc-700 dark:text-zinc-300 text-xs font-bold rounded-xl transition duration-150 cursor-pointer select-none">
                        <!-- Loading Spinner -->
                        <svg wire:loading wire:target="clearAllAssignments" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Default Icon -->
                        <svg wire:loading.remove wire:target="clearAllAssignments" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        <span wire:loading wire:target="clearAllAssignments">Liberando...</span>
                        <span wire:loading.remove wire:target="clearAllAssignments">Limpiar Croquis</span>
                    </button>
                </div>

                <!-- Unassigned Students Sidebar -->
                <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-xs flex flex-col h-[400px]">
                    <div class="flex items-center justify-between mb-3 shrink-0">
                        <h3 class="text-xs font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider">
                            Lista de Espera ({{ count($unassignedStudents) }})
                        </h3>
                    </div>
                    
                    <div class="overflow-y-auto space-y-2 flex-1 pr-1" id="unassigned-students-list">
                        @forelse($unassignedStudents as $student)
                            <div draggable="true"
                                 ondragstart="event.dataTransfer.setData('text/plain', '{{ $student['id'] }}')"
                                 class="p-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-150 dark:border-zinc-800/80 hover:border-violet-300 dark:hover:border-violet-850 hover:bg-violet-50/10 dark:hover:bg-violet-950/5 rounded-xl cursor-grab active:cursor-grabbing transition duration-150 group">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate leading-tight group-hover:text-violet-600 dark:group-hover:text-violet-400">
                                            {{ $student['nombres_apellidos'] }}
                                        </p>
                                        <p class="text-[10px] text-zinc-450 dark:text-zinc-500 mt-0.5 leading-none">
                                            CI: {{ $student['ci'] }}
                                        </p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5">
                                        <!-- Score badge -->
                                        @if($student['nota_final'] !== null)
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md leading-none {{ $student['nota_final'] >= 60 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400' }}">
                                                {{ round($student['nota_final'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none bg-zinc-100 text-zinc-450 dark:bg-zinc-800 dark:text-zinc-500">
                                                N/A
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-zinc-300 dark:text-zinc-750 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-[11px] font-semibold text-zinc-400 dark:text-zinc-500">
                                    Todos los postulantes tienen asiento
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: 2D Classroom Seating Grid -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-xs relative overflow-hidden">
                <!-- Loading Progress Bar -->
                <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-violet-600 animate-pulse"></div>
                
                @if(!$grupo)
                    <div class="flex flex-col items-center justify-center text-center p-12 min-h-[450px]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-zinc-200 dark:text-zinc-800 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                        </svg>
                        <h3 class="text-base font-bold text-zinc-700 dark:text-zinc-300">No se ha seleccionado ningún grupo</h3>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1 max-w-xs">
                            Por favor, asegúrate de que el aula tenga materias/horarios programados e inicializa un grupo.
                        </p>
                    </div>
                @else
                    <!-- Front / Chalkboard Indicator -->
                    <div class="bg-zinc-100 dark:bg-zinc-800/80 text-zinc-500 dark:text-zinc-400 text-xs font-bold py-3 px-4 rounded-xl border border-zinc-200 dark:border-zinc-700/80 text-center mb-8 shadow-xs flex items-center justify-center gap-2 select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 010-3m0 3a1.5 1.5 0 000-3m0 3.75V21m0-11.25V3.75m0 6a1.5 1.5 0 010-3m0 3a1.5 1.5 0 000-3m0 9.75V10.5" />
                        </svg>
                        📢 ESCRIBIR AQUÍ / PIZARRA DEL AULA - FRENTE
                    </div>

                    <!-- Seating Grid -->
                    <div class="grid gap-4" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
                        @for ($i = 1; $i <= $aula->capacidad; $i++)
                            @php
                                $student = $seatingMap[$i] ?? null;
                            @endphp

                            @if($student)
                                <!-- Occupied Seat -->
                                <div draggable="true"
                                     ondragstart="event.dataTransfer.setData('text/plain', '{{ $student['id'] }}')"
                                     ondragover="event.preventDefault()"
                                     ondrop="$wire.assignSeat(event.dataTransfer.getData('text/plain'), {{ $i }})"
                                     class="relative flex flex-col items-center justify-between p-3.5 min-h-[110px] bg-white dark:bg-zinc-800 border-2 border-violet-500 dark:border-violet-400 hover:shadow-md hover:scale-[1.01] hover:border-violet-600 rounded-2xl shadow-sm transition duration-150 group cursor-grab active:cursor-grabbing">
                                    
                                    <!-- Seat Badge -->
                                    <span class="absolute top-1.5 left-2.5 text-[9px] font-black text-zinc-400 dark:text-zinc-500 leading-none">
                                        #{{ $i }}
                                    </span>

                                    <!-- Unassign Button -->
                                    <button wire:click="unassignSeat({{ $student['id'] }})"
                                            type="button"
                                            title="Desocupar asiento"
                                            class="absolute top-1 right-1 p-0.5 rounded-lg text-zinc-300 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all duration-150 opacity-0 group-hover:opacity-100 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <!-- Student Profile Info -->
                                    <div class="flex flex-col items-center justify-center text-center mt-2.5 w-full">
                                        <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-950 text-violet-700 dark:text-violet-400 flex items-center justify-center text-[10px] font-black tracking-tighter shrink-0 border border-violet-200 dark:border-violet-900">
                                            {{ $student['iniciales'] }}
                                        </div>
                                        <p class="text-[10px] font-bold text-zinc-800 dark:text-zinc-200 truncate w-full mt-1.5 leading-tight">
                                            {{ explode(' ', $student['nombres_apellidos'])[0] }}
                                            @if(count(explode(' ', $student['nombres_apellidos'])) > 1)
                                                {{ explode(' ', $student['nombres_apellidos'])[1] }}
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Admitted / Reprobated Indicators -->
                                    <div class="w-full flex items-center justify-center mt-1.5 shrink-0">
                                        @if($student['estado_admision'] === 'admitido_primera_opcion' || $student['estado_admision'] === 'admitido_segunda_opcion')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-[8px] font-bold text-emerald-600 dark:text-emerald-400 leading-none">
                                                <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Admitido
                                            </span>
                                        @elseif($student['estado_admision'] === 'reprobado')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-[8px] font-bold text-rose-600 dark:text-rose-400 leading-none">
                                                Reprobado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/20 text-[8px] font-bold text-amber-600 dark:text-amber-400 leading-none">
                                                Pendiente
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Empty Seat -->
                                <div ondragover="event.preventDefault()"
                                     ondrop="$wire.assignSeat(event.dataTransfer.getData('text/plain'), {{ $i }})"
                                     class="flex flex-col items-center justify-center p-3.5 min-h-[110px] bg-zinc-50/50 dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-800 hover:border-violet-400 dark:hover:border-violet-750 hover:bg-violet-50/5 dark:hover:bg-violet-950/5 hover:shadow-xs rounded-2xl transition duration-150 group select-none">
                                    <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-650 mb-1 leading-none">
                                        #{{ $i }}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-zinc-300 dark:text-zinc-750 group-hover:text-violet-500 dark:group-hover:text-violet-400 transition-colors duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span class="text-[9px] font-semibold text-zinc-400 dark:text-zinc-600 mt-1 select-none">
                                        Asiento libre
                                    </span>
                                </div>
                            @endif

                        @endfor
                    </div>

                    <!-- Teacher / Backrow Indicator -->
                    <div class="text-[9px] text-zinc-400 dark:text-zinc-600 font-bold uppercase tracking-wider text-center mt-8 select-none">
                        🚪 Entrada del Aula (Atrás)
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>
