<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Postulantes</flux:heading>
            <flux:subheading>Lista de postulantes inscritos al CUP</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.carga-lotes') }}" icon="document-arrow-up" variant="filled" class="cursor-pointer select-none">
                Carga Masiva (CSV)
            </flux:button>
            <flux:button wire:click="openCreate" variant="primary" icon="plus" class="cursor-pointer select-none">
                Nuevo Postulante
            </flux:button>
        </div>
    </div>

    <!-- Panel de Exportación y Reportes Personalizado -->
    <div x-data="{
        tabla: 'postulantes',
        gestionId: '',
        carreraId: '',
        formato: 'excel',
        columnas: [],
        columnsMap: {
            carreras: [
                { key: 'id', label: 'ID' },
                { key: 'sigla', label: 'Sigla' },
                { key: 'nombre', label: 'Nombre' },
                { key: 'materias_count', label: 'Materias Habilitadas' }
            ],
            docentes: [
                { key: 'id', label: 'ID' },
                { key: 'nombre', label: 'Nombre Completo' },
                { key: 'email', label: 'Correo electrónico' },
                { key: 'ci', label: 'CI' },
                { key: 'telefono', label: 'Teléfono' },
                { key: 'especialidad', label: 'Especialidad' },
                { key: 'formacion_academica', label: 'Formación' },
                { key: 'profesional_area', label: 'Profesional en Área' },
                { key: 'tiene_maestria', label: 'Tiene Maestría' },
                { key: 'tiene_diplomado', label: 'Tiene Diplomado' }
            ],
            examenes: [
                { key: 'id', label: 'ID' },
                { key: 'nombre', label: 'Examen' },
                { key: 'materia', label: 'Materia' },
                { key: 'carrera', label: 'Carrera' },
                { key: 'gestion', label: 'Gestión' },
                { key: 'docente', label: 'Docente' },
                { key: 'alumnos', label: 'Alumnos / Postulantes' },
                { key: 'ponderacion', label: 'Ponderación' },
                { key: 'fecha', label: 'Fecha' }
            ],
            postulantes: [
                { key: 'id', label: 'ID' },
                { key: 'nombre', label: 'Nombre Completo' },
                { key: 'email', label: 'Correo electrónico' },
                { key: 'ci', label: 'CI' },
                { key: 'telefono', label: 'Teléfono' },
                { key: 'sexo', label: 'Sexo' },
                { key: 'colegio_procedencia', label: 'Colegio' },
                { key: 'ciudad', label: 'Ciudad' },
                { key: 'carrera_primera_opcion', label: '1ra Opción' },
                { key: 'carrera_segunda_opcion', label: '2da Opción' },
                { key: 'gestion', label: 'Gestión' },
                { key: 'nota_final', label: 'Nota Final' },
                { key: 'estado_admision', label: 'Estado' }
            ],
            materias: [
                { key: 'id', label: 'ID' },
                { key: 'sigla', label: 'Sigla' },
                { key: 'nombre', label: 'Nombre' },
                { key: 'carrera', label: 'Carrera' },
                { key: 'docente', label: 'Docente' },
                { key: 'alumnos', label: 'Alumnos / Postulantes' }
            ]
        },
        init() {
            this.resetColumns();
            this.$watch('tabla', () => this.resetColumns());
        },
        resetColumns() {
            this.columnas = this.columnsMap[this.tabla].map(c => c.key);
        },
        getLink() {
            let baseUrl = '{{ route('admin.exportar.personalizado') }}';
            return `${baseUrl}?tabla=${this.tabla}&gestion_id=${this.gestionId}&carrera_id=${this.carreraId}&formato=${this.formato}&columnas=${this.columnas.join(',')}`;
        }
    }" class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-4 mb-5">
            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Generador de Reportes y Exportación</h3>
                <p class="text-xs text-zinc-400">Selecciona el módulo, aplica los filtros correspondientes y exporta en Excel o PDF.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- 1. Módulo / Tabla -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Datos a Exportar</label>
                <select x-model="tabla" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="postulantes">Postulantes</option>
                    <option value="docentes">Docentes</option>
                    <option value="carreras">Carreras</option>
                    <option value="examenes">Exámenes</option>
                    <option value="materias">Materias</option>
                </select>
            </div>

            <!-- 2. Gestión -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Semestre / Gestión</label>
                <select x-model="gestionId" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="">Todas las gestiones</option>
                    @foreach($gestiones as $g)
                        <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Carrera -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Filtrar por Carrera</label>
                <select x-model="carreraId" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="">Todas las carreras</option>
                    @foreach($carreras as $c)
                        <option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Formato -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Formato de Salida</label>
                <select x-model="formato" class="w-full rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm px-3 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    <option value="excel">Excel (CSV)</option>
                    <option value="pdf">Imprimir / PDF</option>
                </select>
            </div>

            <!-- 5. Botón Acción -->
            <div>
                <a :href="getLink()" target="_blank" class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 h-[38px] rounded-xl transition duration-155 shadow-sm cursor-pointer select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Generar Reporte</span>
                </a>
            </div>
        </div>

        <!-- Checkboxes de Columnas / Campos a incluir -->
        <div class="mt-4 border-t border-zinc-150 dark:border-zinc-800 pt-4">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 block mb-2.5">Campos a incluir en el Reporte:</span>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <template x-for="col in columnsMap[tabla]" :key="col.key">
                    <label class="inline-flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 cursor-pointer select-none">
                        <input type="checkbox" :value="col.key" x-model="columnas" class="rounded border-zinc-300 dark:border-zinc-700 text-indigo-650 focus:ring-indigo-500 bg-white dark:bg-zinc-900">
                        <span x-text="col.label"></span>
                    </label>
                </template>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('voice_feedback'))
        <div class="p-4 text-sm text-indigo-700 bg-indigo-50 dark:bg-indigo-950/30 dark:text-indigo-400 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 flex items-center gap-2 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.063.922-2.063 2.063v3.75c0 1.141.922 2.062 2.062 2.062h1.932l4.5 4.5c.944.945 2.56.276 2.56-1.06V4.06zM18.57 17.47a.75.75 0 11-1.06-1.06 4.5 4.5 0 000-6.36.75.75 0 011.06-1.06 6 6 0 010 8.48z" /><path d="M21.4 20.3a.75.75 0 11-1.06-1.06 8.5 8.5 0 000-12.02.75.75 0 111.06-1.06 10 10 0 010 14.14z" /></svg>
            <span>{{ session('voice_feedback') }}</span>
        </div>
    @endif

    @if (session()->has('voice_error'))
        <div class="p-4 text-sm text-rose-700 bg-rose-50 dark:bg-rose-950/30 dark:text-rose-450 rounded-2xl border border-rose-100 dark:border-rose-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>
            <span>{{ session('voice_error') }}</span>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div x-data="voiceSearchWidget($wire)" class="relative flex items-center gap-2 grow">
            <!-- Floating message (centered above the input) -->
            <div 
                x-show="isListening || currentTranscript" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                class="absolute -top-14 left-1/2 -translate-x-1/2 bg-rose-600 dark:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-2xl shadow-xl flex items-center gap-2 border border-rose-500 whitespace-nowrap z-50 animate-bounce"
            >
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                </span>
                <span x-text="currentTranscript"></span>
            </div>

            <div class="grow">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar nombre, CI..." icon="magnifying-glass" class="w-full" />
            </div>
            
            <div class="shrink-0 flex items-center gap-1.5">
                <button 
                    type="button"
                    wire:click="limpiarFiltros"
                    class="h-[38px] w-[38px] rounded-xl border bg-zinc-50 hover:bg-zinc-100 text-zinc-650 border-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-750 dark:text-zinc-300 dark:border-zinc-700 flex items-center justify-center transition-all duration-200 cursor-pointer"
                    title="Limpiar filtros"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <button 
                    type="button"
                    @click="startSpeech()"
                    :class="isListening ? 'bg-rose-600 hover:bg-rose-700 text-white border-rose-700 shadow-md shadow-rose-500/20 animate-pulse' : 'bg-zinc-50 hover:bg-zinc-100 text-zinc-650 border-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-750 dark:text-zinc-300 dark:border-zinc-700'"
                    class="h-[38px] w-[38px] rounded-xl border flex items-center justify-center transition-all duration-200 cursor-pointer relative"
                    title="Filtrar por voz. Ej: 'Sistemas', 'Reprobado', 'Nota mayor a 60', 'Limpiar'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                </button>
            </div>
        </div>
        <flux:select wire:model.live="filterGestion">
            <flux:select.option value="">Todas las gestiones</flux:select.option>
            @foreach($gestiones as $g)
                <flux:select.option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo)(Activa)@endif</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterCarrera">
            <flux:select.option value="">Todas las carreras</flux:select.option>
            @foreach($carreras as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterEstado">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($estadosOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <div class="overflow-x-auto w-full">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Postulante</flux:table.column>
                    <flux:table.column>CI</flux:table.column>
                    <flux:table.column>1ra Opción</flux:table.column>
                    <flux:table.column>2da Opción</flux:table.column>
                    <flux:table.column>Nota Final</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                    <flux:table.column>Requisitos / Pago</flux:table.column>
                    <flux:table.column class="text-center">Cambiar Estado</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($postulantes as $postulante)
                        <flux:table.row :key="$postulante->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-sm flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($postulante->nombres_apellidos ?? $postulante->user?->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-sm">{{ $postulante->nombres_apellidos ?? $postulante->user?->name ?? '—' }}</p>
                                        <p class="text-xs text-zinc-400 truncate">{{ $postulante->user?->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-655 dark:text-zinc-400">{{ $postulante->ci }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300">{{ $postulante->carreraPrimeraOpn?->sigla ?? '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $postulante->carreraSegundaOpn?->sigla ?? '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($postulante->nota_final !== null)
                                    <button wire:click="openNotas({{ $postulante->id }})" class="group flex items-center gap-1 hover:underline cursor-pointer focus:outline-hidden" title="Ver calificaciones detalladas">
                                        <span class="font-bold text-sm {{ $postulante->nota_final >= 60 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ number_format($postulante->nota_final, 2) }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-450 group-hover:text-indigo-500 transition-colors">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-zinc-400 text-sm">&mdash;</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $estadoClasses = [
                                        'pendiente'               => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-900/50',
                                        'admitido_primera_opcion' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900/50',
                                        'admitido_segunda_opcion' => 'bg-teal-50 text-teal-700 border-teal-100 dark:bg-teal-950/30 dark:text-teal-300 dark:border-teal-900/50',
                                        'no_admitido'             => 'bg-zinc-100 text-zinc-650 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700',
                                        'reprobado'               => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-900/50',
                                        'no_presentado'           => 'bg-zinc-100 text-zinc-650 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700',
                                    ];
                                    $cls = $estadoClasses[$postulante->estado_admision] ?? $estadoClasses['pendiente'];
                                    $labels = [
                                        'pendiente' => 'Pendiente',
                                        'admitido_primera_opcion' => 'Admitido 1ra',
                                        'admitido_segunda_opcion' => 'Admitido 2da',
                                        'no_admitido' => 'No admitido',
                                        'reprobado' => 'Reprobado',
                                        'no_presentado' => 'No presentado',
                                    ];
                                    $l = $labels[$postulante->estado_admision] ?? $postulante->estado_admision;
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $cls }}">
                                    {{ $l }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    @if($postulante->habilitado)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                            Habilitado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-850" title="{{ $postulante->mensaje_documentos }}">
                                            Falta documento
                                        </span>
                                    @endif

                                    @if($postulante->pago_realizado)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                                            Pagado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-455 border border-rose-200 dark:border-rose-850">
                                            Sin Pago
                                        </span>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-center">
                                <flux:select wire:change="cambiarEstado({{ $postulante->id }}, $event.target.value)" class="text-xs py-1">
                                    @foreach($estadosUpdateOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $postulante->estado_admision === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="openNotas({{ $postulante->id }})" size="sm" variant="ghost" icon="eye" title="Ver calificaciones" />
                                    <flux:button wire:click="openEdit({{ $postulante->id }})" size="sm" variant="ghost" icon="pencil-square" title="Editar" />
                                    <flux:button wire:click="delete({{ $postulante->id }})" wire:confirm="¿Eliminar este postulante?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" title="Eliminar" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" class="text-center text-zinc-400 py-10">
                                No se encontraron postulantes con los filtros actuales.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $postulantes->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <flux:modal name="postulante-modal" wire:model.self="showModal" class="w-full max-w-xl">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Postulante' : 'Nuevo Postulante' }}</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nombre completo</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Ana Gómez" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Correo electrónico</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="Ej: ana@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Cédula de Identidad (CI)</flux:label>
                    <flux:input wire:model="ci" placeholder="Ej: 1234567" />
                    <flux:error name="ci" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="telefono" placeholder="Ej: 71234567" />
                    <flux:error name="telefono" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de Nacimiento</flux:label>
                    <flux:input type="date" wire:model="fecha_nacimiento" />
                    <flux:error name="fecha_nacimiento" />
                </flux:field>

                <flux:field>
                    <flux:label>Sexo</flux:label>
                    <flux:select wire:model="sexo">
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </flux:select>
                    <flux:error name="sexo" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Dirección</flux:label>
                    <flux:input wire:model="direccion" placeholder="Ej: Av. Las Américas #123" />
                    <flux:error name="direccion" />
                </flux:field>

                <flux:field>
                    <flux:label>Colegio de Procedencia</flux:label>
                    <flux:input wire:model="colegio_procedencia" placeholder="Ej: Colegio Nacional" />
                    <flux:error name="colegio_procedencia" />
                </flux:field>

                <flux:field>
                    <flux:label>Ciudad</flux:label>
                    <flux:input wire:model="ciudad" placeholder="Ej: Santa Cruz" />
                    <flux:error name="ciudad" />
                </flux:field>

                <flux:field>
                    <flux:label>Carrera (1ra Opción)</flux:label>
                    <flux:select wire:model="carrera_primera_opcion_id">
                        <option value="">Seleccione una carrera...</option>
                        @foreach($carreras as $c)
                            <option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="carrera_primera_opcion_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Carrera (2da Opción)</flux:label>
                    <flux:select wire:model="carrera_segunda_opcion_id">
                        <option value="">Seleccione carrera (opcional)...</option>
                        @foreach($carreras as $c)
                            <option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="carrera_segunda_opcion_id" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Gestión Académica</flux:label>
                    <flux:select wire:model="gestion_id">
                        <option value="">Seleccione gestión...</option>
                        @foreach($gestiones as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo)(Activa)@endif</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="gestion_id" />
                </flux:field>
            </div>

            <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-850">
                <flux:heading size="sm" class="font-bold">Requisitos de Inscripción Presentados</flux:heading>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <flux:checkbox wire:model="ci_vigente" label="CI Vigente" />
                    <flux:checkbox wire:model="titulo_bachiller" label="Título Bachiller" />
                    <flux:checkbox wire:model="libreta_legalizada" label="Libreta Legalizada" />
                </div>
            </div>

            <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-850">
                <flux:heading size="sm" class="font-bold">Estado de Pagos</flux:heading>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <flux:checkbox wire:model="pago_realizado" label="Pago de Inscripción Realizado" />
                    <flux:checkbox wire:model="pago_matricula_realizado" label="Pago de Matrícula Realizado" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-850">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Inscribir Postulante' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Modal Ver Calificaciones Detalladas -->
    @if($showNotasModal && $selectedPostulante)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-950/40 dark:bg-zinc-950/60 backdrop-blur-xs transition-opacity z-40" wire:click="$set('showNotasModal', false)"></div>

            <!-- Content Container -->
            <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl p-6 md:p-8 animate-fade-in z-50">
                <!-- Accent Bar -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-indigo-505"></div>

                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Calificaciones de Exámenes</h3>
                        <p class="text-xs text-zinc-400 mt-1">Postulante: {{ $selectedPostulante->nombres_apellidos }} &bull; CI: {{ $selectedPostulante->ci }}</p>
                    </div>
                    <button wire:click="$set('showNotasModal', false)" type="button" class="p-1.5 rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="space-y-6">
                    <div class="bg-zinc-50 dark:bg-zinc-950/30 border border-zinc-150 dark:border-zinc-850 p-4 rounded-2xl flex justify-between items-center text-xs">
                        <div>
                            <span class="text-zinc-450 block uppercase tracking-wider font-semibold">Carrera Primera Opción</span>
                            <span class="text-zinc-800 dark:text-zinc-200 font-bold">{{ $selectedPostulante->carreraPrimeraOpn?->nombre }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-zinc-450 block uppercase tracking-wider font-semibold">Nota Final Admisión</span>
                            <span class="text-lg font-black {{ $selectedPostulante->nota_final >= 60 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-450' }}">
                                {{ $selectedPostulante->nota_final !== null ? number_format($selectedPostulante->nota_final, 2) : '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-2">Materia</th>
                                    <th class="py-3 px-2 text-center">1er Parcial (30%)</th>
                                    <th class="py-3 px-2 text-center">2do Parcial (30%)</th>
                                    <th class="py-3 px-2 text-center">Examen Final (40%)</th>
                                    <th class="py-3 px-2 text-right">Nota Materia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                @foreach($postulanteNotas as $mNota)
                                    <tr class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10">
                                        <td class="py-3.5 px-2 font-bold">{{ $mNota['materia_nombre'] }}</td>
                                        <td class="py-3.5 px-2 text-center font-medium">
                                            {{ $mNota['primer_parcial'] !== null ? number_format($mNota['primer_parcial'], 1) : '—' }}
                                        </td>
                                        <td class="py-3.5 px-2 text-center font-medium">
                                            {{ $mNota['segundo_parcial'] !== null ? number_format($mNota['segundo_parcial'], 1) : '—' }}
                                        </td>
                                        <td class="py-3.5 px-2 text-center font-medium">
                                            {{ $mNota['examen_final'] !== null ? number_format($mNota['examen_final'], 1) : '—' }}
                                        </td>
                                        <td class="py-3.5 px-2 text-right font-black">
                                            <span class="{{ $mNota['total_materia'] >= 60 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-455' }}">
                                                {{ number_format($mNota['total_materia'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-zinc-150 dark:border-zinc-850">
                    <flux:button wire:click="$set('showNotasModal', false)" variant="ghost">Cerrar</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>


