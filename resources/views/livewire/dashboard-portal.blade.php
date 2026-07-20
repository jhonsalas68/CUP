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

                @if (!$postulante->pago_realizado)
                    <!-- STRIPE PAYMENT PROMPT -->
                    <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-3xl shadow-xl flex flex-col items-center justify-center text-center space-y-6">
                        <div class="h-16 w-16 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center border border-indigo-100 dark:border-indigo-900/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </div>
                        <div class="max-w-md space-y-2">
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">Pago de Inscripción Requerido</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tu perfil ha sido registrado, pero para poder elegir tus materias y grupos debes pagar tu matrícula de inscripción de la gestión académica.</p>
                        </div>
                        <div class="w-full max-w-sm bg-zinc-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-zinc-150 dark:border-zinc-800 text-left space-y-3">
                            <div class="flex justify-between items-center text-xs font-bold text-zinc-400 uppercase tracking-widest">
                                <span>Concepto</span>
                                <span>Monto</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold text-zinc-800 dark:text-zinc-200 py-2 border-t border-b border-zinc-150 dark:border-zinc-800">
                                <span>Derecho de Admisión y Matrícula</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-extrabold text-base">500.00 BOB</span>
                            </div>
                            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-relaxed">
                                * El pago se realiza de forma 100% segura. Aceptamos tarjetas de débito/crédito nacionales e internacionales.
                            </p>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('stripe.checkout') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-650 hover:bg-indigo-700 active:scale-95 text-white font-bold rounded-xl transition duration-150 shadow-md shadow-indigo-600/10">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" /><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v14.25c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 19.125V4.875zM21 9.75H3V18a.75.75 0 00.75.75h16.5A.75.75 0 0021 18V9.75zM3 7.5h18V6a.75.75 0 00-.75-.75H3.75A.75.75 0 003 6v1.5z" clip-rule="evenodd" /></svg>
                                <span>Pagar Inscripción con Stripe</span>
                            </a>
                        </div>
                    </div>
                @elseif (!$isEnrolled && !$postulante->habilitado)
                    <!-- DOCUMENT VALIDATION PENDING -->
                    <div class="lg:col-span-3 bg-amber-50/50 dark:bg-amber-950/10 border border-amber-200 dark:border-amber-900/40 p-8 rounded-3xl flex flex-col sm:flex-row items-start gap-5 shadow-xs">
                        <div class="h-12 w-12 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 rounded-xl flex items-center justify-center shrink-0 border border-amber-200/50 dark:border-amber-800/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="space-y-3">
                            <h3 class="text-base font-bold text-amber-900 dark:text-amber-400 tracking-tight">Habilitación de Documentos Pendiente</h3>
                            <p class="text-sm text-amber-800/80 dark:text-amber-300/80 leading-relaxed">
                                El pago de matrícula se ha realizado correctamente. Sin embargo, tu perfil aún no está habilitado por el Administrador. Debes presentar los requisitos físicos en la ventanilla de Admisiones.
                            </p>
                            @if ($postulante->mensaje_documentos)
                                <div class="p-4 bg-white dark:bg-zinc-900 border border-amber-200/50 dark:border-amber-900/30 rounded-xl space-y-1">
                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest block">Observación del Administrador</span>
                                    <p class="text-xs text-zinc-700 dark:text-zinc-300 font-semibold">{{ $postulante->mensaje_documentos }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif (!$isEnrolled)
                    <!-- SELF-ENROLLMENT SELECTOR -->
                    <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-3xl shadow-xl space-y-6">
                        <div class="space-y-1.5 pb-4 border-b border-zinc-150 dark:border-zinc-800/60">
                            <h3 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Inscripción de Materias</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Selecciona el grupo de tu preferencia para cada materia del plan de estudios.</p>
                        </div>

                        <form wire:submit.prevent="enroll" class="space-y-6">
                            <div class="space-y-6">
                                @foreach($availableGroupsByMateria as $materiaId => $data)
                                    <div class="p-5 bg-zinc-50 dark:bg-zinc-800/20 rounded-2xl border border-zinc-150 dark:border-zinc-850/80 space-y-4">
                                        <div class="flex justify-between items-center">
                                            <div class="space-y-0.5">
                                                <h4 class="font-bold text-base text-zinc-800 dark:text-zinc-100 leading-snug">{{ $data['materia']->nombre }}</h4>
                                                <span class="text-xs text-indigo-650 dark:text-indigo-400 font-bold uppercase tracking-wider">{{ $data['materia']->sigla }}</span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @forelse($data['groups'] as $grupo)
                                                <label class="relative border rounded-xl p-4 flex items-center justify-between cursor-pointer transition duration-150 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 select-none
                                                    {{ ($selectedGroups[$materiaId] ?? '') == $grupo->id ? 'bg-indigo-50/40 border-indigo-450 dark:bg-indigo-950/20 dark:border-indigo-850 shadow-sm' : 'border-zinc-200 dark:border-zinc-800' }}">
                                                    
                                                    <div class="flex items-center gap-3">
                                                        <input type="radio" wire:model="selectedGroups.{{ $materiaId }}" value="{{ $grupo->id }}" class="text-indigo-600 focus:ring-indigo-500 border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 h-4.5 w-4.5 cursor-pointer">
                                                        <div class="space-y-1">
                                                            <span class="font-extrabold text-sm text-zinc-850 dark:text-zinc-150">Grupo: {{ $grupo->nombre }}</span>
                                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-450 leading-relaxed space-y-0.5">
                                                                <p>&bull; Docente: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $grupo->docentes->first()?->nombre ?? 'Coordinador Académico' }}</span></p>
                                                                <p>&bull; Horarios: 
                                                                    @forelse($grupo->horarios as $h)
                                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $h->dia_semana }} ({{ substr($h->hora_inicio,0,5) }}-{{ substr($h->hora_fin,0,5) }}) [Aula {{ $h->aula }}]</span>@if(!$loop->last), @endif
                                                                    @empty
                                                                        <span class="italic text-zinc-400">Sin horarios</span>
                                                                    @endforelse
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-right shrink-0 ml-4">
                                                        <span class="text-xs font-bold block
                                                            @if($grupo->current_postulantes_count >= $grupo->cupo_maximo) text-rose-500
                                                            @elseif($grupo->cupo_maximo - $grupo->current_postulantes_count <= 5) text-amber-500
                                                            @else text-emerald-500 @endif">
                                                            {{ $grupo->cupo_maximo - $grupo->current_postulantes_count }} / {{ $grupo->cupo_maximo }} cupos
                                                        </span>
                                                    </div>
                                                </label>
                                            @empty
                                                <div class="col-span-2 text-center py-4 bg-white dark:bg-zinc-900/50 rounded-xl text-xs text-zinc-400 border border-dashed border-zinc-250 dark:border-zinc-800">
                                                    No hay grupos configurados para esta materia en la gestión activa.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="px-8 py-3.5 bg-indigo-650 hover:bg-indigo-700 active:scale-95 text-white font-bold rounded-xl text-sm transition duration-155 shadow-md shadow-indigo-600/10 cursor-pointer select-none">
                                    Confirmar Inscripción a Materias
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    @if (in_array($postulante->estado_admision, ['admitido_primera_opcion', 'admitido_segunda_opcion']) && !$postulante->pago_matricula_realizado)
                        <!-- STRIPE MATRICULA PAYMENT PROMPT -->
                        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-3xl shadow-xl flex flex-col items-center justify-center text-center space-y-6">
                            <div class="h-16 w-16 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-450 rounded-2xl flex items-center justify-center border border-emerald-100 dark:border-emerald-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                            </div>
                            <div class="max-w-md space-y-2">
                                <h3 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">¡Felicidades! Has sido Admitido</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Has aprobado satisfactoriamente todos tus exámenes y has sido admitido al CUP. Para completar tu inscripción definitiva y habilitar tu registro universitario, debes realizar el pago de tu matrícula de admisión.</p>
                            </div>
                            <div class="w-full max-w-sm bg-zinc-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-zinc-150 dark:border-zinc-800 text-left space-y-3">
                                <div class="flex justify-between items-center text-xs font-bold text-zinc-400 uppercase tracking-widest">
                                    <span>Concepto</span>
                                    <span>Monto</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold text-zinc-800 dark:text-zinc-200 py-2 border-t border-b border-zinc-150 dark:border-zinc-800">
                                    <span>Matrícula Universitaria (Admisión CUP)</span>
                                    <span class="text-emerald-600 dark:text-emerald-450 font-extrabold text-base">1000.00 BOB</span>
                                </div>
                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 leading-relaxed">
                                    * El pago se realiza de forma 100% segura mediante Stripe Checkout.
                                </p>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('stripe.checkout') }}?type=matricula" class="inline-flex items-center gap-2 px-8 py-3 bg-emerald-650 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl transition duration-150 shadow-md shadow-emerald-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" /><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v14.25c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 19.125V4.875zM21 9.75H3V18a.75.75 0 00.75.75h16.5A.75.75 0 0021 18V9.75zM3 7.5h18V6a.75.75 0 00-.75-.75H3.75A.75.75 0 003 6v1.5z" clip-rule="evenodd" /></svg>
                                    <span>Pagar Matrícula con Stripe</span>
                                </a>
                            </div>
                        </div>
                    @else
                        @if (in_array($postulante->estado_admision, ['admitido_primera_opcion', 'admitido_segunda_opcion']) && $postulante->pago_matricula_realizado)
                            <!-- CELEBRATION SUCCESS BANNER -->
                            <div class="lg:col-span-3 p-5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-3xl border border-emerald-400/30 shadow-md flex items-center gap-4 animate-fade-in mb-6">
                                <div class="h-12 w-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-white"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-base">¡Inscripción Universitaria Completada!</h4>
                                    <p class="text-xs text-white/90 font-medium">Felicidades. Has sido matriculado con éxito y eres oficialmente estudiante de la universidad.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Tab Navigation for enrolled student -->
                        <div class="lg:col-span-3 flex flex-wrap gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2 mb-6">
                            <button type="button" wire:click="selectPortalTab('grades')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 {{ $activePortalTab === 'grades' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                                Calificaciones y Horario
                            </button>
                            <button type="button" wire:click="selectPortalTab('attendance')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 {{ $activePortalTab === 'attendance' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                                Mi Asistencia
                            </button>
                            <button type="button" wire:click="selectPortalTab('appeals')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 {{ $activePortalTab === 'appeals' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                                Reclamos de Notas
                            </button>
                            <button type="button" wire:click="selectPortalTab('notifications')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 relative {{ $activePortalTab === 'notifications' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                                Notificaciones
                                @if($notifications->where('leido', false)->count() > 0)
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
                                        {{ $notifications->where('leido', false)->count() }}
                                    </span>
                                @endif
                            </button>
                        </div>

                        @if($activePortalTab === 'grades')
                            <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
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
                                                                <div class="inline-flex items-center gap-1.5 justify-center">
                                                                    <span class="{{ $row['primer_parcial'] >= 60 ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-zinc-500' }}">{{ number_format($row['primer_parcial'], 1) }}</span>
                                                                    <button type="button" wire:click="openAppeal({{ $row['primer_parcial_id'] }}, 'Primer Parcial', '{{ $row['materia'] }}', {{ $row['primer_parcial'] }})" class="p-0.5 text-zinc-400 hover:text-indigo-650 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded transition cursor-pointer" title="Reclamar o Solicitar revisión">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.12 2.9 2.5 3.25L3 16.5v.75A2.25 2.25 0 005.25 19.5h13.5A2.25 2.25 0 0021 17.25V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75v5.26z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="py-4 text-center font-semibold text-sm">
                                                            @if (is_null($row['segundo_parcial']))
                                                                <span class="text-zinc-300 dark:text-zinc-700">&mdash;</span>
                                                            @else
                                                                <div class="inline-flex items-center gap-1.5 justify-center">
                                                                    <span class="{{ $row['segundo_parcial'] >= 60 ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-zinc-500' }}">{{ number_format($row['segundo_parcial'], 1) }}</span>
                                                                    <button type="button" wire:click="openAppeal({{ $row['segundo_parcial_id'] }}, 'Segundo Parcial', '{{ $row['materia'] }}', {{ $row['segundo_parcial'] }})" class="p-0.5 text-zinc-400 hover:text-indigo-650 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded transition cursor-pointer" title="Reclamar o Solicitar revisión">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.12 2.9 2.5 3.25L3 16.5v.75A2.25 2.25 0 005.25 19.5h13.5A2.25 2.25 0 0021 17.25V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75v5.26z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="py-4 text-center font-semibold text-sm">
                                                            @if (is_null($row['examen_final']))
                                                                <span class="text-zinc-300 dark:text-zinc-700">&mdash;</span>
                                                            @else
                                                                <div class="inline-flex items-center gap-1.5 justify-center">
                                                                    <span class="{{ $row['examen_final'] >= 60 ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-zinc-500' }}">{{ number_format($row['examen_final'], 1) }}</span>
                                                                    <button type="button" wire:click="openAppeal({{ $row['examen_final_id'] }}, 'Examen Final', '{{ $row['materia'] }}', {{ $row['examen_final'] }})" class="p-0.5 text-zinc-400 hover:text-indigo-650 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded transition cursor-pointer" title="Reclamar o Solicitar revisión">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.12 2.9 2.5 3.25L3 16.5v.75A2.25 2.25 0 005.25 19.5h13.5A2.25 2.25 0 0021 17.25V6.75A2.25 2.25 0 0018.75 4.5H5.25A2.25 2.25 0 003 6.75v5.26z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
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
                                    <div class="mt-6 p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-150 dark:border-zinc-800 text-[11px] text-zinc-400 dark:text-zinc-500 space-y-1">
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
                                                        <div class="text-xs font-bold text-zinc-700 dark:text-zinc-300 truncate">Docente: {{ $grupo->docentes->first()?->nombre ?? $grupo->docentes->first()?->user?->name ?? 'No asignado' }}</div>
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

                                <!-- Card: Mis Pagos -->
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs space-y-4">
                                    <div class="space-y-1">
                                        <h3 class="text-base font-bold text-zinc-900 dark:text-white tracking-tight">Historial de Pagos</h3>
                                        <p class="text-xs text-zinc-400">Verifica el estado de tus transacciones y matrículas.</p>
                                    </div>

                                    <div class="space-y-3">
                                        @if ($postulante->pago_realizado)
                                            <div class="p-4 bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-150 dark:border-emerald-900/30 rounded-xl space-y-3">
                                                <div class="flex justify-between items-start">
                                                    <div class="space-y-0.5">
                                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Matrícula de Inscripción</span>
                                                        <h4 class="font-bold text-sm text-zinc-850 dark:text-zinc-150">Derecho de Admisión y Matrícula</h4>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold border border-emerald-100 dark:border-emerald-900/30">
                                                        Pagado
                                                    </span>
                                                </div>

                                                <div class="text-xs text-zinc-500 dark:text-zinc-450 border-t border-dashed border-zinc-200 dark:border-zinc-800 pt-3 space-y-1.5">
                                                    <div class="flex justify-between">
                                                        <span>Monto:</span>
                                                        <span class="font-bold text-zinc-850 dark:text-zinc-200">500.00 BOB</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Pasarela:</span>
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">Stripe Checkout</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Código Recibo:</span>
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">CUP-INS-{{ str_pad($postulante->id, 6, '0', STR_PAD_LEFT) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($postulante->pago_matricula_realizado)
                                            <div class="p-4 bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-150 dark:border-emerald-900/30 rounded-xl space-y-3">
                                                <div class="flex justify-between items-start">
                                                    <div class="space-y-0.5">
                                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Matrícula Universitaria</span>
                                                        <h4 class="font-bold text-sm text-zinc-850 dark:text-zinc-150">Admisión Definitiva</h4>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold border border-emerald-100 dark:border-emerald-900/30">
                                                        Pagado
                                                    </span>
                                                </div>

                                                <div class="text-xs text-zinc-500 dark:text-zinc-450 border-t border-dashed border-zinc-200 dark:border-zinc-800 pt-3 space-y-1.5">
                                                    <div class="flex justify-between">
                                                        <span>Monto:</span>
                                                        <span class="font-bold text-zinc-850 dark:text-zinc-200">1000.00 BOB</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Pasarela:</span>
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">Stripe Checkout</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Código Recibo:</span>
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">CUP-MAT-{{ str_pad($postulante->id, 6, '0', STR_PAD_LEFT) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if (!$postulante->pago_realizado && !$postulante->pago_matricula_realizado)
                                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/20 border border-zinc-200 dark:border-zinc-800 rounded-xl text-center py-6">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mx-auto text-zinc-400 mb-2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                                </svg>
                                                <span class="text-xs text-zinc-400 block font-semibold">Sin pagos registrados</span>
                                                <span class="text-[10px] text-zinc-400 block mt-1">Debes completar el pago de tus matrículas.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($activePortalTab === 'attendance')
                            <!-- RENDER ATTENDANCE TAB FOR POSTULANTE -->
                            <div class="lg:col-span-3 space-y-6 animate-fade-in">
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                                    <div class="space-y-1 mb-6">
                                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Mi Control de Asistencia</h3>
                                        <p class="text-xs text-zinc-400">Revisa tu asistencia acumulada por materia para garantizar el cumplimiento del reglamento.</p>
                                    </div>

                                    @if(count($asistenciasStats) > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            @foreach($asistenciasStats as $gId => $stat)
                                                <div class="p-4 bg-zinc-50 dark:bg-zinc-950/20 border border-zinc-150 dark:border-zinc-800 rounded-xl space-y-2">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-200">{{ $stat['materia'] }}</h4>
                                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">{{ $stat['grupo'] }}</span>
                                                        </div>
                                                        <span class="text-lg font-black {{ $stat['tasa'] >= 80 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $stat['tasa'] }}%</span>
                                                    </div>
                                                    <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-1.5">
                                                        <div class="h-1.5 rounded-full {{ $stat['tasa'] >= 80 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $stat['tasa'] }}%"></div>
                                                    </div>
                                                    <div class="flex justify-between text-[10px] text-zinc-400 font-semibold pt-1">
                                                        <span>Presente: {{ $stat['presente'] }}</span>
                                                        <span>Licencia: {{ $stat['licencia'] }}</span>
                                                        <span>Faltas: {{ $stat['falta'] }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Detailed Logs Table -->
                                    <div class="mt-8 space-y-4">
                                        <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-250">Historial Detallado de Clases</h4>
                                        <div class="overflow-x-auto border border-zinc-150 dark:border-zinc-800 rounded-xl">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450">
                                                    <tr>
                                                        <th class="py-2.5 px-4">Fecha</th>
                                                        <th class="py-2.5 px-4">Materia</th>
                                                        <th class="py-2.5 px-4">Grupo</th>
                                                        <th class="py-2.5 px-4 text-right">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850 text-zinc-700 dark:text-zinc-300">
                                                    @forelse($asistencias as $asist)
                                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                                            <td class="py-2.5 px-4 font-semibold">{{ $asist->fecha->format('d/m/Y') }}</td>
                                                            <td class="py-2.5 px-4">{{ $asist->grupo->materia->nombre }}</td>
                                                            <td class="py-2.5 px-4">{{ $asist->grupo->nombre }}</td>
                                                            <td class="py-2.5 px-4 text-right">
                                                                @if($asist->estado === 'presente')
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 text-[10px] font-bold border border-emerald-100 dark:border-emerald-900/30">Presente</span>
                                                                @elseif($asist->estado === 'licencia')
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-450 text-[10px] font-bold border border-amber-100 dark:border-amber-900/30">Licencia</span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 text-[10px] font-bold border border-rose-100 dark:border-rose-900/30">Falta</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center py-6 text-zinc-400">No se encontraron registros de asistencia.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($activePortalTab === 'appeals')
                            <!-- RENDER GRADE APPEALS TAB FOR POSTULANTE -->
                            <div class="lg:col-span-3 space-y-6 animate-fade-in">
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                                    <div class="space-y-1 mb-6">
                                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Mis Reclamos y Solicitudes de Revisión</h3>
                                        <p class="text-xs text-zinc-400">Monitorea el estado de tus solicitudes de revisión de calificaciones presentadas a los docentes.</p>
                                    </div>

                                    <div class="overflow-x-auto border border-zinc-150 dark:border-zinc-800 rounded-xl">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450">
                                                <tr>
                                                    <th class="py-2.5 px-4">Fecha</th>
                                                    <th class="py-2.5 px-4">Evaluación</th>
                                                    <th class="py-2.5 px-4">Justificación</th>
                                                    <th class="py-2.5 px-4 text-center">Nota Original / Nueva</th>
                                                    <th class="py-2.5 px-4 text-center">Estado</th>
                                                    <th class="py-2.5 px-4 text-right">Observación Docente</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850 text-zinc-700 dark:text-zinc-300">
                                                @forelse($appeals as $appeal)
                                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                                        <td class="py-3 px-4 font-semibold">{{ $appeal->created_at->format('d/m/Y') }}</td>
                                                        <td class="py-3 px-4">
                                                            <div class="font-bold text-zinc-800 dark:text-zinc-250">{{ $appeal->examen->nombre }}</div>
                                                            <div class="text-[10px] text-zinc-450 font-bold uppercase tracking-wider">{{ $appeal->examen->materia->nombre }}</div>
                                                        </td>
                                                        <td class="py-3 px-4 max-w-xs truncate" title="{{ $appeal->descripcion }}">{{ $appeal->descripcion }}</td>
                                                        <td class="py-3 px-4 text-center font-bold">
                                                            <span>{{ number_format($appeal->nota_anterior, 1) }}</span>
                                                            @if($appeal->estado === 'aceptado')
                                                                <span class="text-emerald-500 font-bold ml-1">→ {{ number_format($appeal->nota_nueva, 1) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-3 px-4 text-center">
                                                            @if($appeal->estado === 'pendiente')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 text-[10px] font-bold border border-amber-150">Pendiente</span>
                                                            @elseif($appeal->estado === 'en_revision')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 text-[10px] font-bold border border-indigo-150">En Revisión</span>
                                                            @elseif($appeal->estado === 'aceptado')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 text-[10px] font-bold border border-emerald-150">Aceptado</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-450 text-[10px] font-bold border border-rose-150">Rechazado</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-3 px-4 text-right max-w-xs truncate" title="{{ $appeal->respuesta_docente ?? 'Sin observaciones' }}">
                                                            {{ $appeal->respuesta_docente ?? '—' }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-6 text-zinc-400">No has registrado ningún reclamo de nota.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($activePortalTab === 'notifications')
                            <!-- RENDER NOTIFICATIONS TAB FOR POSTULANTE -->
                            <div class="lg:col-span-3 space-y-6 animate-fade-in">
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-xs">
                                    <div class="space-y-1 mb-6">
                                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Centro de Notificaciones</h3>
                                        <p class="text-xs text-zinc-400">Mantente al tanto de las alertas importantes de tu proceso de admisión.</p>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse($notifications as $notif)
                                            <div wire:click="markNotificationRead({{ $notif->id }})" class="p-4 rounded-xl border flex items-start gap-3 transition hover:bg-zinc-50 dark:hover:bg-zinc-850/30 cursor-pointer select-none {{ $notif->leido ? 'bg-zinc-50/50 dark:bg-zinc-950/15 border-zinc-200 dark:border-zinc-800/80 opacity-75' : 'bg-indigo-50/20 dark:bg-indigo-950/10 border-indigo-150 dark:border-indigo-900/40 shadow-2xs font-semibold' }}">
                                                <div class="h-2 w-2 rounded-full mt-1.5 shrink-0 {{ $notif->leido ? 'bg-zinc-350 dark:bg-zinc-700' : 'bg-rose-500 animate-pulse' }}"></div>
                                                <div class="grow space-y-0.5">
                                                    <div class="flex justify-between items-center text-xs">
                                                        <span class="font-bold text-zinc-800 dark:text-zinc-150">{{ $notif->titulo }}</span>
                                                        <span class="text-[10px] text-zinc-400">{{ $notif->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-normal">{{ $notif->mensaje }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-8 text-zinc-400 text-sm">No tienes notificaciones registradas.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
        @endif
    @endif

    <!-- ==================== DOCENTE VIEW ==================== -->
    @if ($role === 'Docente')
        <!-- Tabs for Docente -->
        <div class="flex flex-wrap gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2 mb-6">
            <button type="button" wire:click="selectPortalTab('grades')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 {{ in_array($activePortalTab, ['grades', 'attendance', 'topics']) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                Mis Grupos Académicos
            </button>
            <button type="button" wire:click="selectPortalTab('appeals')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 {{ $activePortalTab === 'appeals' ? 'bg-indigo-650 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                Reclamos de Alumnos
            </button>
            <button type="button" wire:click="selectPortalTab('notifications')" class="py-2.5 px-4 font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 relative {{ $activePortalTab === 'notifications' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-550 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                Notificaciones
                @if($notifications->where('leido', false)->count() > 0)
                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
                        {{ $notifications->where('leido', false)->count() }}
                    </span>
                @endif
            </button>
        </div>

        @if(in_array($activePortalTab, ['grades', 'attendance', 'topics']))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-fade-in">
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
                        <!-- Inner Sub-Tabs: Calificaciones, Asistencia, Temas -->
                        <div class="flex border-b border-zinc-150 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/20 px-6 py-2 gap-2">
                            <button type="button" wire:click="selectPortalTab('grades')" class="py-2 px-3 text-xs font-bold transition rounded-lg {{ $activePortalTab === 'grades' ? 'bg-white dark:bg-zinc-900 text-indigo-650 dark:text-indigo-400 shadow-2xs' : 'text-zinc-400 hover:text-zinc-600' }}">Calificaciones</button>
                            <button type="button" wire:click="selectPortalTab('attendance')" class="py-2 px-3 text-xs font-bold transition rounded-lg {{ $activePortalTab === 'attendance' ? 'bg-white dark:bg-zinc-900 text-indigo-650 dark:text-indigo-400 shadow-2xs' : 'text-zinc-400 hover:text-zinc-600' }}">Control de Asistencia</button>
                            <button type="button" wire:click="selectPortalTab('topics')" class="py-2 px-3 text-xs font-bold transition rounded-lg {{ $activePortalTab === 'topics' ? 'bg-white dark:bg-zinc-900 text-indigo-650 dark:text-indigo-400 shadow-2xs' : 'text-zinc-400 hover:text-zinc-600' }}">Avance de Materia</button>
                        </div>

                        @if($activePortalTab === 'grades')
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
                                                        {{ $student->nombres_apellidos ?? $student->user?->name ?? '—' }}
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
                        @endif

                        @if($activePortalTab === 'attendance')
                            <!-- Teacher Attendance Sheet Panel -->
                            <div class="p-6 space-y-6 animate-fade-in">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <label for="attendanceDate" class="text-xs font-bold text-zinc-500 uppercase shrink-0">Fecha Clase:</label>
                                        <input type="date" id="attendanceDate" wire:model.live="attendanceDate" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-1.5 text-xs text-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                    <button type="button" wire:click="loadAttendance" class="text-xs font-bold text-indigo-650 hover:underline">Recargar Asistencia</button>
                                </div>

                                <form wire:submit.prevent="saveAttendance" class="space-y-4">
                                    <div class="overflow-x-auto border border-zinc-150 dark:border-zinc-800 rounded-xl">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450">
                                                <tr>
                                                    <th class="py-2.5 px-4">Alumno</th>
                                                    <th class="py-2.5 px-4">CI</th>
                                                    <th class="py-2.5 px-4 text-right w-64">Estado de Asistencia</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                                @foreach($selectedGrupo->postulantes as $student)
                                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                                        <td class="py-3 px-4 font-semibold text-zinc-800 dark:text-zinc-150">
                                                            {{ $student->nombres_apellidos }}
                                                        </td>
                                                        <td class="py-3 px-4 text-zinc-450">
                                                            {{ $student->ci }}
                                                        </td>
                                                        <td class="py-3 px-4 text-right">
                                                            <div class="inline-flex rounded-lg border border-zinc-200 dark:border-zinc-800 p-0.5 bg-zinc-50 dark:bg-zinc-900">
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model="attendanceInput.{{ $student->id }}" value="presente" class="sr-only peer" />
                                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md block peer-checked:bg-emerald-600 peer-checked:text-white text-zinc-400 hover:text-zinc-650">Presente</span>
                                                                </label>
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model="attendanceInput.{{ $student->id }}" value="licencia" class="sr-only peer" />
                                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md block peer-checked:bg-amber-600 peer-checked:text-white text-zinc-400 hover:text-zinc-650">Licencia</span>
                                                                </label>
                                                                <label class="cursor-pointer">
                                                                    <input type="radio" wire:model="attendanceInput.{{ $student->id }}" value="falta" class="sr-only peer" />
                                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md block peer-checked:bg-rose-600 peer-checked:text-white text-zinc-400 hover:text-zinc-650">Falta</span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-800">
                                        <button type="submit" class="px-5 py-2 bg-indigo-650 hover:bg-indigo-750 text-white font-bold rounded-xl text-xs transition duration-150 shadow-sm cursor-pointer select-none">
                                            Guardar Asistencia
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($activePortalTab === 'topics')
                            <!-- Teacher Topic Logging Panel -->
                            <div class="p-6 space-y-6 animate-fade-in">
                                <form wire:submit.prevent="saveTopic" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="sm:col-span-1 space-y-1">
                                            <label for="topicDate" class="text-xs font-bold text-zinc-500 uppercase block">Fecha:</label>
                                            <input type="date" id="topicDate" wire:model="topicDate" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-2 text-xs text-zinc-800 dark:text-zinc-100" />
                                        </div>
                                        <div class="sm:col-span-2 space-y-1">
                                            <label for="topicTema" class="text-xs font-bold text-zinc-500 uppercase block">Tema Avanzado:</label>
                                            <input type="text" id="topicTema" wire:model="topicTema" placeholder="Ej. Límites algebraicos y continuidad" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-2 text-xs text-zinc-800 dark:text-zinc-100" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label for="topicDescripcion" class="text-xs font-bold text-zinc-500 uppercase block">Descripción del Contenido (Opcional):</label>
                                        <textarea id="topicDescripcion" wire:model="topicDescripcion" rows="3" placeholder="Detalle los puntos o ejercicios resueltos en clase..." class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-2 text-xs text-zinc-800 dark:text-zinc-100"></textarea>
                                    </div>

                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-755 text-white font-bold rounded-xl text-xs transition duration-150 shadow-md cursor-pointer select-none">
                                            Registrar Avance
                                        </button>
                                    </div>
                                </form>

                                <!-- Historical avance list -->
                                <div class="border-t border-zinc-150 dark:border-zinc-800 pt-6 space-y-4">
                                    <h4 class="text-sm font-bold text-zinc-850 dark:text-zinc-200">Historial de Avance de Materia</h4>
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                                        @forelse($controlTemas->where('grupo_id', $selectedGrupo->id) as $tema)
                                            <div class="p-3 bg-zinc-50 dark:bg-zinc-950/20 border border-zinc-150 dark:border-zinc-800 rounded-xl space-y-1">
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="font-bold text-zinc-800 dark:text-zinc-150">{{ $tema->tema }}</span>
                                                    <span class="text-[10px] text-zinc-400 font-bold">{{ $tema->fecha->format('d/m/Y') }}</span>
                                                </div>
                                                @if($tema->descripcion)
                                                    <p class="text-[11px] text-zinc-500 leading-normal">{{ $tema->descripcion }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center py-6 text-zinc-400 text-xs">No se han registrado temas de avance para este grupo.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif
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

        @if($activePortalTab === 'appeals')
            <!-- Teacher Appeals Management Table (full-width) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-xs p-6 space-y-4 animate-fade-in">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Reclamos y Solicitudes de Revisión de Notas</h3>
                    <p class="text-xs text-zinc-400">Atiende y resuelve los reclamos de calificaciones de tus alumnos.</p>
                </div>

                <div class="overflow-x-auto border border-zinc-150 dark:border-zinc-800 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450">
                            <tr>
                                <th class="py-2.5 px-4 font-bold">Estudiante</th>
                                <th class="py-2.5 px-4 font-bold">Evaluación</th>
                                <th class="py-2.5 px-4 font-bold">Justificación de Alumno</th>
                                <th class="py-2.5 px-4 text-center font-bold">Nota Examen</th>
                                <th class="py-2.5 px-4 text-center font-bold">Estado</th>
                                <th class="py-2.5 px-4 text-right font-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850 text-zinc-750 dark:text-zinc-300">
                            @forelse($appeals as $appeal)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                    <td class="py-3 px-4 font-bold">
                                        {{ $appeal->postulante->nombres_apellidos }}
                                        <span class="text-[10px] text-zinc-400 font-normal block">CI: {{ $appeal->postulante->ci }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-zinc-850 dark:text-zinc-250">{{ $appeal->examen->nombre }}</div>
                                        <div class="text-[10px] text-zinc-450 font-bold uppercase tracking-wider">{{ $appeal->examen->materia->nombre }}</div>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate" title="{{ $appeal->descripcion }}">{{ $appeal->descripcion }}</td>
                                    <td class="py-3 px-4 text-center font-bold">
                                        <span>{{ number_format($appeal->nota_anterior, 1) }}</span>
                                        @if($appeal->estado === 'aceptado')
                                            <span class="text-emerald-500 font-bold ml-1">→ {{ number_format($appeal->nota_nueva, 1) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($appeal->estado === 'pendiente')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 text-[10px] font-bold border border-amber-150">Pendiente</span>
                                        @elseif($appeal->estado === 'aceptado')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 text-[10px] font-bold border border-emerald-150">Aceptado</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-450 text-[10px] font-bold border border-rose-150">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium">
                                        @if($appeal->estado === 'pendiente')
                                            <button type="button" wire:click="loadAppealToResolve({{ $appeal->id }})" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-[10px] transition cursor-pointer select-none">
                                                Resolver Reclamo
                                            </button>
                                        @else
                                            <span class="text-[10px] text-zinc-400 font-semibold" title="{{ $appeal->respuesta_docente }}">Resuelto</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-zinc-450">No se han presentado solicitudes de revisión de notas para tus materias.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($activePortalTab === 'notifications')
            <!-- Teacher Notifications list -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 space-y-4 animate-fade-in">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Centro de Notificaciones</h3>
                    <p class="text-xs text-zinc-400">Alertas importantes y notificaciones del sistema para docentes.</p>
                </div>

                <div class="space-y-3">
                    @forelse($notifications as $notif)
                        <div wire:click="markNotificationRead({{ $notif->id }})" class="p-4 rounded-xl border flex items-start gap-3 transition hover:bg-zinc-50 dark:hover:bg-zinc-850/30 cursor-pointer select-none {{ $notif->leido ? 'bg-zinc-50/50 dark:bg-zinc-950/15 border-zinc-200 dark:border-zinc-800/80 opacity-75' : 'bg-indigo-50/20 dark:bg-indigo-950/10 border-indigo-150 dark:border-indigo-900/40 shadow-2xs font-semibold' }}">
                            <div class="h-2 w-2 rounded-full mt-1.5 shrink-0 {{ $notif->leido ? 'bg-zinc-350 dark:bg-zinc-700' : 'bg-rose-500 animate-pulse' }}"></div>
                            <div class="grow space-y-0.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-zinc-800 dark:text-zinc-150">{{ $notif->titulo }}</span>
                                    <span class="text-[10px] text-zinc-400">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-normal">{{ $notif->mensaje }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400 text-sm font-medium">No tienes notificaciones registradas.</div>
                    @endforelse
                </div>
            </div>
        @endif
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
        </div>
    @endif

    <!-- Modal de Reclamo (Student Appeal Form) -->
    @if($showAppealModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-950/40 dark:bg-zinc-950/60 backdrop-blur-xs transition-opacity z-40" wire:click="$set('showAppealModal', false)"></div>

            <!-- Content Container -->
            <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 md:p-8 animate-fade-in z-50">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-indigo-650"></div>

                <!-- Header -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Solicitud de Revisión de Calificación</h3>
                        <p class="text-xs text-zinc-400 mt-1">Presenta un reclamo justificado al docente de la materia.</p>
                    </div>
                    <button wire:click="$set('showAppealModal', false)" type="button" class="p-1 rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-850 hover:text-zinc-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-950/20 border border-zinc-150 dark:border-zinc-850 rounded-xl space-y-1.5 text-xs">
                        <div><span class="font-bold text-zinc-500">Materia:</span> <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $appealMateriaNombre }}</span></div>
                        <div><span class="font-bold text-zinc-500">Evaluación:</span> <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $appealExamenNombre }}</span></div>
                        <div><span class="font-bold text-zinc-500">Calificación Registrada:</span> <span class="font-bold text-rose-600">{{ number_format($appealNotaAnterior, 1) }} pts</span></div>
                    </div>

                    <form wire:submit.prevent="submitAppeal" class="space-y-4">
                        <div class="space-y-1">
                            <label for="appealDescripcion" class="text-xs font-bold text-zinc-500 uppercase block">Motivo o Justificación del Reclamo:</label>
                            <textarea id="appealDescripcion" wire:model="appealDescripcion" rows="4" placeholder="Detalla el motivo de tu reclamo y explica por qué consideras que la nota es incorrecta..." class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-855 bg-transparent px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"></textarea>
                            @error('appealDescripcion') <span class="text-rose-600 text-[10px] font-bold block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="appealArchivo" class="text-xs font-bold text-zinc-500 uppercase block">Adjuntar Evidencia (Opcional, PDF o Imagen):</label>
                            <input type="file" id="appealArchivo" wire:model="appealArchivo" class="w-full text-xs text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/30 dark:file:text-indigo-400 file:cursor-pointer" />
                            <div wire:loading wire:target="appealArchivo" class="text-[10px] text-zinc-450 font-bold block mt-1">Cargando archivo...</div>
                            @error('appealArchivo') <span class="text-rose-600 text-[10px] font-bold block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-850">
                            <button wire:click="$set('showAppealModal', false)" type="button" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-bold hover:bg-zinc-200">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-650 hover:bg-indigo-750 text-white font-bold rounded-xl text-xs transition duration-150 shadow-sm cursor-pointer select-none">
                                Enviar Reclamo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para Resolver Reclamo (Teacher Resolve Form) -->
    @if($selectedAppealId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-950/40 dark:bg-zinc-950/60 backdrop-blur-xs transition-opacity z-40" wire:click="$set('selectedAppealId', null)"></div>

            <!-- Content Container -->
            <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 md:p-8 animate-fade-in z-50">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-indigo-650"></div>

                <!-- Header -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Resolución de Reclamo de Nota</h3>
                        <p class="text-xs text-zinc-400 mt-1">Evalúa la solicitud presentada por el postulante.</p>
                    </div>
                    <button wire:click="$set('selectedAppealId', null)" type="button" class="p-1 rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-850 hover:text-zinc-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Appeal Details -->
                    @php
                        $activeAppeal = \App\Models\ReclamoNota::with(['postulante', 'examen.materia'])->find($selectedAppealId);
                    @endphp
                    @if($activeAppeal)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-950/20 border border-zinc-150 dark:border-zinc-850 rounded-xl space-y-2 text-xs">
                            <div><span class="font-bold text-zinc-500">Estudiante:</span> <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $activeAppeal->postulante->nombres_apellidos }}</span></div>
                            <div><span class="font-bold text-zinc-500">Materia / Evaluación:</span> <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $activeAppeal->examen->materia->nombre }} &bull; {{ $activeAppeal->examen->nombre }}</span></div>
                            <div><span class="font-bold text-zinc-500">Nota Anterior:</span> <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ number_format($activeAppeal->nota_anterior, 1) }} pts</span></div>
                            <div class="pt-2 border-t border-zinc-200/50 dark:border-zinc-800/50">
                                <span class="font-bold text-zinc-500 block mb-1">Motivo del estudiante:</span>
                                <p class="text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-zinc-150 dark:border-zinc-800 leading-normal">{{ $activeAppeal->descripcion }}</p>
                            </div>
                            @if($activeAppeal->archivo_adjunto)
                                <div class="pt-2">
                                    <span class="font-bold text-zinc-500 font-semibold block mb-1">Evidencia adjunta:</span>
                                    <a href="{{ asset('storage/' . $activeAppeal->archivo_adjunto) }}" target="_blank" class="inline-flex items-center gap-1.5 text-indigo-650 hover:underline font-bold bg-indigo-50 dark:bg-indigo-950/30 px-3 py-1.5 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                        <span>Descargar Evidencia</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form wire:submit.prevent="resolveAppeal" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="appealStatus" class="text-xs font-bold text-zinc-500 uppercase block">Resolución:</label>
                                <select id="appealStatus" wire:model.live="appealStatus" class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-850 bg-transparent px-3 py-2 text-zinc-800 dark:text-zinc-100">
                                    <option value="aceptado">Aceptar Reclamo (Modificar Nota)</option>
                                    <option value="rechazado">Rechazar Reclamo (Mantener Nota)</option>
                                </select>
                            </div>
                            
                            @if($appealStatus === 'aceptado')
                                <div class="space-y-1">
                                    <label for="appealNewGrade" class="text-xs font-bold text-zinc-500 uppercase block">Nueva Nota (0 - 100):</label>
                                    <input type="number" step="0.01" min="0" max="100" id="appealNewGrade" wire:model="appealNewGrade" class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-850 bg-transparent px-3 py-2 text-zinc-800 dark:text-zinc-100 font-bold" />
                                    @error('appealNewGrade') <span class="text-rose-600 text-[10px] font-bold block">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <label for="appealResponseComment" class="text-xs font-bold text-zinc-500 uppercase block">Comentario u Observación Docente:</label>
                            <textarea id="appealResponseComment" wire:model="appealResponseComment" rows="3" placeholder="Explique brevemente los motivos de la resolución..." class="w-full text-xs rounded-xl border border-zinc-200 dark:border-zinc-855 bg-transparent px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"></textarea>
                            @error('appealResponseComment') <span class="text-rose-600 text-[10px] font-bold block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-850">
                            <button wire:click="$set('selectedAppealId', null)" type="button" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-bold hover:bg-zinc-200">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-650 hover:bg-indigo-755 text-white font-bold rounded-xl text-xs transition duration-150 shadow-sm cursor-pointer select-none">
                                Guardar Resolución
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
