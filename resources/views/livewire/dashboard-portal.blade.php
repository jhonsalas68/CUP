<div class="space-y-8">
    <!-- Success & Error Alert Banners -->
    @if ($successMessage)
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-3 animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-emerald-600">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-semibold">{{ $successMessage }}</span>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 bg-rose-50 dark:bg-rose-950/30 text-rose-800 dark:text-rose-400 rounded-2xl border border-rose-100 dark:border-rose-900/50 flex items-center gap-3 animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-rose-600">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-semibold">{{ $errorMessage }}</span>
        </div>
    @endif

    <!-- ==================== POSTULANTE VIEW ==================== -->
    @if ($role === 'Postulante')
        @if (!$postulante)
            <!-- Complete Profile Registration Form for registered users who lack a Postulante model -->
            <div class="max-w-2xl mx-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl rounded-3xl overflow-hidden">
                <div class="relative h-2 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Completa tu Registro de Postulante</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Ingresa tus datos personales y académicos para habilitar tu cuenta en el proceso de admisión.</p>
                    </div>

                    <form wire:submit.prevent="registerPostulante" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Documento de Identidad -->
                            <div class="space-y-1">
                                <label for="ci" class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Cédula de Identidad (CI)</label>
                                <input type="text" id="ci" wire:model="ci" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-transparent px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100" placeholder="Ej. 8765432" />
                                @error('ci') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="space-y-1">
                                <label for="telefono" class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Teléfono / Celular</label>
                                <input type="text" id="telefono" wire:model="telefono" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-transparent px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100" placeholder="Ej. 76543210" />
                                @error('telefono') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Fecha de Nacimiento -->
                        <div class="space-y-1">
                            <label for="fecha_nacimiento" class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" wire:model="fecha_nacimiento" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-transparent px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100" />
                            @error('fecha_nacimiento') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Primera Opción -->
                            <div class="space-y-1">
                                <label for="carrera_primera_opcion_id" class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Carrera (Primera Opción)</label>
                                <select id="carrera_primera_opcion_id" wire:model="carrera_primera_opcion_id" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100">
                                    <option value="">Selecciona una carrera...</option>
                                    @foreach($carrerasDisponibles as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }} ({{ $carrera->sigla }})</option>
                                    @endforeach
                                </select>
                                @error('carrera_primera_opcion_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Segunda Opción -->
                            <div class="space-y-1">
                                <label for="carrera_segunda_opcion_id" class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Carrera (Segunda Opción - Opcional)</label>
                                <select id="carrera_segunda_opcion_id" wire:model="carrera_segunda_opcion_id" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100">
                                    <option value="">Ninguna</option>
                                    @foreach($carrerasDisponibles as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }} ({{ $carrera->sigla }})</option>
                                    @endforeach
                                </select>
                                @error('carrera_segunda_opcion_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold rounded-xl transition duration-150 shadow-md shadow-indigo-600/10">
                                Confirmar y Postular
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Postulante Dashboard -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Academic Profile Summary Card -->
                <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-2xl shadow-xs border border-indigo-100 dark:border-indigo-900/50">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">{{ auth()->user()->name }}</h2>
                                <span class="text-[10px] font-semibold uppercase tracking-wider bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 px-2 py-0.5 rounded-md">Postulante</span>
                            </div>
                            <p class="text-xs text-zinc-400">CI: {{ $postulante->ci }} &bull; Teléfono: {{ $postulante->telefono }} &bull; Fecha Nac: {{ $postulante->fecha_nacimiento?->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-400">Semestre Académico: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $postulante->gestion->nombre }}</span></p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto">
                        <div class="text-left md:text-right space-y-1">
                            <div class="text-xs font-semibold text-zinc-400 uppercase tracking-widest">Estado del Proceso</div>
                            @if ($postulante->estado_admision === 'admitido_primera_opcion')
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-100 dark:border-emerald-900/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                    Admitido - 1ra Opción ({{ $postulante->carreraPrimeraOpn->sigla }})
                                </div>
                            @elseif ($postulante->estado_admision === 'admitido_segunda_opcion')
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 text-xs font-bold border border-teal-100 dark:border-teal-900/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-ping"></span>
                                    Admitido - 2da Opción ({{ $postulante->carreraSegundaOpn?->sigla }})
                                </div>
                            @elseif ($postulante->estado_admision === 'reprobado')
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 text-xs font-bold border border-rose-100 dark:border-rose-900/50">
                                    No Admitido
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 text-xs font-bold border border-amber-100 dark:border-amber-900/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Validación / Cursando Exámenes
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Column 1: Mis Calificaciones y Exámenes (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                        <div class="space-y-1 mb-6">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Planilla de Exámenes y Notas</h3>
                            <p class="text-xs text-zinc-400">Revisa tus calificaciones del semestre en curso bajo las ponderaciones académicas oficiales.</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <th class="pb-3 text-xs font-bold text-zinc-400 uppercase tracking-wider">Materia</th>
                                        <th class="pb-3 text-center text-xs font-bold text-zinc-400 uppercase tracking-wider">P. Parcial (30%)</th>
                                        <th class="pb-3 text-center text-xs font-bold text-zinc-400 uppercase tracking-wider">S. Parcial (30%)</th>
                                        <th class="pb-3 text-center text-xs font-bold text-zinc-400 uppercase tracking-wider">Ex. Final (40%)</th>
                                        <th class="pb-3 class=text-center text-xs font-bold text-zinc-400 uppercase tracking-wider">Nota Final</th>
                                        <th class="pb-3 text-right text-xs font-bold text-zinc-400 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                    @forelse($gradesTable as $row)
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                            <td class="py-4">
                                                <div class="font-semibold text-sm text-zinc-955 dark:text-zinc-100">{{ $row['materia'] }}</div>
                                                <div class="text-[10px] font-bold text-zinc-400 tracking-wider">{{ $row['sigla'] }}</div>
                                            </td>
                                            <td class="py-4 text-center font-semibold text-sm">
                                                @if (is_null($row['primer_parcial']))
                                                    <span class="text-zinc-300 dark:text-zinc-700">&mdash;</span>
                                                @else
                                                    <span class="{{ $row['primer_parcial'] >= 60 ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500' }}">{{ number_format($row['primer_parcial'], 1) }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-center font-semibold text-sm">
                                                @if (is_null($row['segundo_parcial']))
                                                    <span class="text-zinc-300 dark:text-zinc-700">&mdash;</span>
                                                @else
                                                    <span class="{{ $row['segundo_parcial'] >= 60 ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500' }}">{{ number_format($row['segundo_parcial'], 1) }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-center font-semibold text-sm">
                                                @if (is_null($row['examen_final']))
                                                    <span class="text-zinc-300 dark:text-zinc-700">&mdash;</span>
                                                @else
                                                    <span class="{{ $row['examen_final'] >= 60 ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500' }}">{{ number_format($row['examen_final'], 1) }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-center font-bold text-sm">
                                                <span class="{{ $row['final_grade'] >= 60 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    {{ number_format($row['final_grade'], 2) }}
                                                </span>
                                            </td>
                                            <td class="py-4 text-right">
                                                @if($row['status'] === 'Aprobado')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-100 dark:border-emerald-900/30">
                                                        Aprobado
                                                    </span>
                                                @elseif($row['status'] === 'Reprobado')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 text-xs font-bold border border-rose-100 dark:border-rose-900/30">
                                                        Reprobado
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-50 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 text-xs font-semibold border border-slate-200 dark:border-zinc-700">
                                                        Cursando
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-zinc-400 text-sm">
                                                No se encontraron materias registradas para tu plan de estudios.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Academic Note -->
                        <div class="mt-6 p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400 dark:text-zinc-500 space-y-1">
                            <span class="font-bold uppercase tracking-wider block text-zinc-500 dark:text-zinc-400 mb-1">Criterio de Evaluación Académica</span>
                            <p>&bull; La nota final de cada materia se calcula con la fórmula: <strong>(1er Parcial &times; 30%) + (2do Parcial &times; 30%) + (Examen Final &times; 40%)</strong>.</p>
                            <p>&bull; Para obtener la admisión al CUP, debes aprobar <strong>todas</strong> las materias de tu carrera con una nota promedio final mínima de <strong>60.00 puntos</strong> por materia.</p>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Mis Horarios y Grupos (1/3 width) -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                        <div class="space-y-1 mb-6">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Horario y Aulas</h3>
                            <p class="text-xs text-zinc-400">Consulta tus grupos de clase y aulas asignadas.</p>
                        </div>

                        <div class="space-y-4">
                            @forelse($assignedGroups as $grupo)
                                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-150 dark:border-zinc-800/80 rounded-xl space-y-3">
                                    <div class="flex justify-between items-start">
                                        <div class="space-y-0.5">
                                            <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-100">{{ $grupo->materia->nombre }}</h4>
                                            <span class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Grupo: {{ $grupo->nombre }}</span>
                                        </div>
                                    </div>

                                    <!-- Docente info -->
                                    <div class="flex items-center gap-2 border-t border-b border-zinc-100 dark:border-zinc-800/50 py-2.5">
                                        <div class="h-7 w-7 rounded-lg bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-xs font-bold text-zinc-600 dark:text-zinc-400">
                                            D
                                        </div>
                                        <div class="space-y-0.5 min-w-0">
                                            <div class="text-xs font-bold text-zinc-700 dark:text-zinc-300 truncate">Docente: {{ $grupo->docentes->first()?->user->name ?? 'No asignado' }}</div>
                                            <div class="text-[10px] text-zinc-400 truncate">{{ $grupo->docentes->first()?->especialidad ?? 'Coordinador Académico' }}</div>
                                        </div>
                                    </div>

                                    <!-- Schedules list -->
                                    <div class="space-y-1.5">
                                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Horarios</span>
                                        @forelse($grupo->horarios as $h)
                                            <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-400">
                                                <span>{{ $h->dia_semana }}</span>
                                                <span class="font-semibold">{{ substr($h->hora_inicio, 0, 5) }} - {{ substr($h->hora_fin, 0, 5) }}</span>
                                                <span class="bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 px-2 py-0.5 rounded text-[10px] font-bold">Aula {{ $h->aula }}</span>
                                            </div>
                                        @empty
                                            <span class="text-[10px] text-zinc-400">No hay horarios registrados para este grupo.</span>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 bg-zinc-50 dark:bg-zinc-800/25 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mx-auto text-zinc-400 mb-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    <span class="text-xs text-zinc-400 block font-semibold">Grupos pendientes de asignación.</span>
                                    <span class="text-[10px] text-zinc-400 block">El proceso automático se ejecutará antes del inicio del semestre.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- ==================== DOCENTE VIEW ==================== -->
    @if ($role === 'Docente')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Docente Info & Groups List (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Docente Profile Summary -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-14 w-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xl border border-indigo-100 dark:border-indigo-900/50">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="space-y-0.5">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">{{ auth()->user()->name }}</h2>
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-900/30">Cuerpo Docente</span>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5 text-xs text-zinc-500 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">Especialidad:</span> {{ $docente->especialidad }}</div>
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">CI:</span> {{ $docente->ci }}</div>
                        <div><span class="font-semibold text-zinc-700 dark:text-zinc-300">Contacto:</span> {{ $docente->telefono }}</div>
                    </div>
                </div>

                <!-- My Groups Grid -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white tracking-tight">Mis Grupos y Clases</h3>
                        <p class="text-xs text-zinc-400">Selecciona un grupo para calificar a los alumnos.</p>
                    </div>

                    <div class="space-y-3">
                        @forelse($assignedGroups as $grupo)
                            <div wire:click="selectGrupo({{ $grupo->id }})" 
                                 class="p-4 rounded-xl border transition-all duration-150 cursor-pointer flex justify-between items-center {{ $selectedGrupoId == $grupo->id ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-300 dark:border-indigo-800' : 'bg-zinc-50 dark:bg-zinc-800/40 border-zinc-150 dark:border-zinc-800/80 hover:bg-zinc-100/50 dark:hover:bg-zinc-850/50' }}">
                                <div class="space-y-1">
                                    <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-100 leading-snug">{{ $grupo->materia->nombre }}</h4>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 px-1.5 py-0.5 rounded">Grupo {{ $grupo->nombre }}</span>
                                        <span class="text-[10px] text-zinc-400 font-semibold">{{ $grupo->postulantes->count() }} Alumnos</span>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </div>
                        @empty
                            <div class="text-center py-6 text-zinc-400 text-xs">
                                No tienes grupos académicos asignados para esta gestión.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Grading Panel (7 cols) -->
            <div class="lg:col-span-7">
                @if ($selectedGrupo)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
                        <!-- Top Accent header -->
                        <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-100 dark:border-zinc-800/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h3 class="text-base font-bold text-zinc-900 dark:text-white leading-tight">Calificar Grupo: {{ $selectedGrupo->nombre }}</h3>
                                <p class="text-xs text-zinc-400 font-semibold mt-0.5">{{ $selectedGrupo->materia->nombre }}</p>
                            </div>
                            
                            <!-- Select Exam Type -->
                            <div class="flex items-center gap-2">
                                <label for="selectedExamenTipo" class="text-xs font-bold text-zinc-500 uppercase">Examen:</label>
                                <select id="selectedExamenTipo" wire:model.live="selectedExamenTipo" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-xs font-bold px-3 py-1.5 text-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                    <option value="Primer Parcial">1er Parcial (30%)</option>
                                    <option value="Segundo Parcial">2do Parcial (30%)</option>
                                    <option value="Examen Final">Ex. Final (40%)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Students Grade Entry List -->
                        <div class="p-6 space-y-6">
                            <!-- Optional Date field for creating exam -->
                            <div class="flex items-center gap-3">
                                <label for="fechaExamen" class="text-xs font-bold text-zinc-500 uppercase shrink-0">Fecha Examen:</label>
                                <input type="date" id="fechaExamen" wire:model="fechaExamen" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-1.5 text-xs text-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            </div>

                            <form wire:submit.prevent="saveGrades" class="space-y-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-zinc-150 dark:border-zinc-800">
                                                <th class="pb-2 text-xs font-bold text-zinc-400 uppercase tracking-wider">Alumno</th>
                                                <th class="pb-2 text-center text-xs font-bold text-zinc-400 uppercase tracking-wider">CI</th>
                                                <th class="pb-2 text-right text-xs font-bold text-zinc-400 uppercase tracking-wider w-32">Nota (0 - 100)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/40">
                                            @foreach($selectedGrupo->postulantes as $student)
                                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                                    <td class="py-3 font-semibold text-sm text-zinc-850 dark:text-zinc-150">
                                                        {{ $student->user->name }}
                                                    </td>
                                                    <td class="py-3 text-center text-xs text-zinc-400">
                                                        {{ $student->ci }}
                                                    </td>
                                                    <td class="py-3 text-right">
                                                        <input type="number" 
                                                               step="0.01" 
                                                               min="0" 
                                                               max="100" 
                                                               wire:model="gradesInput.{{ $student->id }}" 
                                                               class="w-24 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-1 text-center text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-zinc-800 dark:text-zinc-100" 
                                                               placeholder="&mdash;" />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                    <button type="button" wire:click="$set('selectedGrupoId', null)" class="px-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-bold text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition duration-150 shadow-md shadow-indigo-600/10">
                                        Guardar Calificaciones
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="h-64 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl flex flex-col items-center justify-center p-6 text-center bg-white dark:bg-zinc-900 shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-zinc-300 dark:text-zinc-700 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-300">Selecciona un Grupo Académico</h4>
                        <p class="text-xs text-zinc-400 max-w-xs mt-1">Haz clic en alguno de los grupos asignados en la columna izquierda para registrar las calificaciones.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ==================== REGULAR REGISTERED USER VIEW ==================== -->
    @if ($role !== 'Postulante' && $role !== 'Docente' && $role !== 'Administrador' && $role !== 'Coordinador')
        <div class="max-w-md mx-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl rounded-3xl overflow-hidden p-8 space-y-6 text-center">
            <div class="h-16 w-16 mx-auto rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>

            <div class="space-y-2">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">Bienvenido al Portal Académico</h2>
                <p class="text-xs text-zinc-500 leading-relaxed">Tu cuenta ha sido creada exitosamente. Sin embargo, aún no tienes asignado un perfil académico (Postulante o Docente).</p>
            </div>

            <div class="space-y-3 pt-4">
                <button wire:click="$set('role', 'Postulante')" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition">
                    Completar Perfil de Postulante
                </button>
                <div class="text-[10px] text-zinc-400">
                    Si eres docente o administrativo, por favor solicita la asignación de tu rol al administrador del sistema.
                </div>
            </div>
        </div>
    @endif
</div>
