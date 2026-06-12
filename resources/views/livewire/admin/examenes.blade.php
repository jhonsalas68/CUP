<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Exámenes y Calificaciones</flux:heading>
            <flux:subheading>Gestión de calificaciones por materia e individuales por postulante</flux:subheading>
        </div>
        @if($activeTab === 'configuracion')
            <flux:button wire:click="openCreate" variant="primary" icon="plus">
                Nuevo Examen
            </flux:button>
        @endif
    </div>

    <!-- Pestañas de Navegación -->
    <div class="flex border-b border-zinc-200 dark:border-zinc-800">
        <button wire:click="$set('activeTab', 'calificaciones')" class="py-2.5 px-4 font-semibold text-sm border-b-2 transition-all duration-150 {{ $activeTab === 'calificaciones' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-350' }} cursor-pointer">
            Calificaciones por Postulante
        </button>
        <button wire:click="$set('activeTab', 'configuracion')" class="py-2.5 px-4 font-semibold text-sm border-b-2 transition-all duration-150 {{ $activeTab === 'configuracion' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-350' }} cursor-pointer">
            Configuración de Exámenes (Ponderaciones)
        </button>
    </div>

    <!-- Panel de Exportación y Reportes Personalizado -->
    <div x-data="{
        tabla: 'examenes',
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
                    @foreach($carrerasList as $c)
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
                <a :href="getLink()" target="_blank" class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-650 hover:bg-indigo-700 text-white px-4 py-2 h-[38px] rounded-xl transition duration-155 shadow-sm cursor-pointer select-none">
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
                        <input type="checkbox" :value="col.key" x-model="columnas" class="rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 bg-white dark:bg-zinc-900">
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

    <!-- Active Filters Panel -->
    @if ($search !== '' || $filterMateria !== '' || $filterGestion !== '' || $filterNotaMin !== '' || $filterNotaMax !== '')
        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/10 border border-indigo-150 dark:border-indigo-900/30 rounded-2xl flex flex-wrap items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 rounded-xl shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3.792 2.938A49.069 49.069 0 0112 2.25c2.774 0 5.485.23 8.12.674a2.25 2.25 0 011.883 2.202v2.154a2.25 2.25 0 01-.659 1.591l-5.399 5.399a2.25 2.25 0 00-.659 1.59v3.137a2.25 2.25 0 01-.89 1.78l-2.6 1.95a.75.75 0 01-1.21-.59v-6.277a2.25 2.25 0 00-.659-1.59L3.57 8.887a2.25 2.25 0 01-.659-1.59V5.14a2.25 2.25 0 011.883-2.203z" clip-rule="evenodd" /></svg>
                </div>
                <div class="space-y-0.5">
                    <span class="text-xs font-black text-indigo-900 dark:text-indigo-400 uppercase tracking-wider block">Filtros de Búsqueda Activos</span>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @if ($search !== '')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 px-2.5 py-0.5 rounded-lg shadow-sm">
                                Texto: "{{ $search }}"
                                <button type="button" wire:click="$set('search', '')" class="text-zinc-400 hover:text-rose-500 font-extrabold ml-1 focus:outline-hidden">&times;</button>
                            </span>
                        @endif
                        @if ($filterMateria !== '')
                            @php $mat = $materias->find($filterMateria); @endphp
                            @if ($mat)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 px-2.5 py-0.5 rounded-lg shadow-sm">
                                    Materia: {{ $mat->sigla }}
                                    <button type="button" wire:click="$set('filterMateria', '')" class="text-zinc-400 hover:text-rose-500 font-extrabold ml-1 focus:outline-hidden">&times;</button>
                                </span>
                            @endif
                        @endif
                        @if ($filterGestion !== '')
                            @php $gest = $gestiones->find($filterGestion); @endphp
                            @if ($gest)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 px-2.5 py-0.5 rounded-lg shadow-sm">
                                    Gestión: {{ $gest->nombre }}
                                    <button type="button" wire:click="$set('filterGestion', '')" class="text-zinc-400 hover:text-rose-500 font-extrabold ml-1 focus:outline-hidden">&times;</button>
                                </span>
                            @endif
                        @endif
                        @if ($filterNotaMin !== '')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 px-2.5 py-0.5 rounded-lg shadow-sm">
                                Nota Mín: {{ $filterNotaMin }} pts
                                <button type="button" wire:click="$set('filterNotaMin', '')" class="text-zinc-400 hover:text-rose-500 font-extrabold ml-1 focus:outline-hidden">&times;</button>
                            </span>
                        @endif
                        @if ($filterNotaMax !== '')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 px-2.5 py-0.5 rounded-lg shadow-sm">
                                Nota Máx: {{ $filterNotaMax }} pts
                                <button type="button" wire:click="$set('filterNotaMax', '')" class="text-zinc-400 hover:text-rose-500 font-extrabold ml-1 focus:outline-hidden">&times;</button>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <button type="button" wire:click="limpiarFiltros" class="text-xs font-bold text-indigo-700 dark:text-indigo-400 hover:text-rose-500 transition cursor-pointer select-none">
                    Limpiar Todos
                </button>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="flex-1 flex items-center gap-2">
            <div x-data="voiceSearchWidget($wire)" class="relative flex items-center gap-2 grow">
                <!-- Floating message (centered above the input) -->
                <div 
                    x-show="isListening && currentTranscript" 
                    x-cloak
                    class="absolute -top-14 left-1/2 -translate-x-1/2 bg-rose-600 dark:bg-rose-700 text-white text-xs font-bold px-4 py-2.5 rounded-2xl shadow-xl flex items-center gap-2 border border-rose-500 whitespace-nowrap z-50 animate-bounce"
                >
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                    <span x-text="currentTranscript"></span>
                </div>

                <div class="grow">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar..." icon="magnifying-glass" class="w-full" />
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
                        title="Filtrar por voz"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="sm:w-52">
            <flux:select wire:model.live="filterGestion">
                <flux:select.option value="">Todas las gestiones</flux:select.option>
                @foreach($gestiones as $g)
                    <flux:select.option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo)(Activa)@endif</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="sm:w-52">
            <flux:select wire:model.live="filterMateria">
                <flux:select.option value="">Todas las materias</flux:select.option>
                @foreach($materias as $m)
                    <flux:select.option value="{{ $m->id }}">{{ $m->sigla }} - {{ $m->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        @if($activeTab === 'calificaciones')
            <div class="flex gap-2 items-center sm:w-44">
                <flux:input wire:model.live.debounce.300ms="filterNotaMin" type="number" min="0" max="100" placeholder="Nota Mín" />
                <span class="text-zinc-400 text-xs">—</span>
                <flux:input wire:model.live.debounce.300ms="filterNotaMax" type="number" min="0" max="100" placeholder="Nota Máx" />
            </div>
        @endif
    </div>

    <!-- Tabla Principal (Calificaciones o Configuración) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>

        @if($activeTab === 'calificaciones')
            <!-- TAB: Calificaciones por Postulante -->
            <div class="overflow-x-auto w-full">
                <flux:table>
                <flux:table.columns>
                    <flux:table.column>Postulante</flux:table.column>
                    <flux:table.column>Grupo</flux:table.column>
                    <flux:table.column>Materia</flux:table.column>
                    <flux:table.column>Docente</flux:table.column>
                    <flux:table.column class="text-center">1er Parcial (30%)</flux:table.column>
                    <flux:table.column class="text-center">2do Parcial (30%)</flux:table.column>
                    <flux:table.column class="text-center">Ex. Final (40%)</flux:table.column>
                    <flux:table.column class="text-center">Nota Ponderada</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($calificaciones as $item)
                        @php
                            $postulante = $item->postulante;
                            $grupo = $item->grupo;
                            $materia = $grupo->materia;
                            $exams = $materia->examenes->where('gestion_id', $grupo->gestion_id);
                            
                            $exam1 = $exams->where('nombre', 'Primer Parcial')->first();
                            $nota1 = $exam1 ? $postulante->notas->where('examen_id', $exam1->id)->first()?->puntaje : null;

                            $exam2 = $exams->where('nombre', 'Segundo Parcial')->first();
                            $nota2 = $exam2 ? $postulante->notas->where('examen_id', $exam2->id)->first()?->puntaje : null;

                            $exam3 = $exams->where('nombre', 'Examen Final')->first();
                            $nota3 = $exam3 ? $postulante->notas->where('examen_id', $exam3->id)->first()?->puntaje : null;

                            $total = 0;
                            foreach ($exams as $exam) {
                                $val = $postulante->notas->where('examen_id', $exam->id)->first()?->puntaje;
                                if ($val !== null) {
                                    $total += ($val * ($exam->ponderacion / 100.00));
                                }
                            }
                            $total = round($total, 2);
                        @endphp
                        <flux:table.row :key="'calif-' . $item->id">
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $postulante->nombres_apellidos }}</span>
                                    <span class="text-xs text-zinc-400">CI: {{ $postulante->ci }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $grupo->nombre }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $materia->nombre }}</span>
                                    <span class="text-xs text-zinc-400">{{ $materia->carrera?->sigla ?? '' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-650 dark:text-zinc-400">
                                {{ $grupo->docentes->pluck('nombre')->implode(', ') ?: 'No asignado' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $nota1 !== null ? round($nota1, 2) : '—' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $nota2 !== null ? round($nota2, 2) : '—' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $nota3 !== null ? round($nota3, 2) : '—' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold
                                    {{ $total >= 60.00 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900/50' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700' }}">
                                    {{ $total }} pts
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-1.5">
                                    <!-- Ver Detalle Postulante -->
                                    <flux:button wire:click="openDetail({{ $postulante->id }})" size="sm" variant="ghost" icon="eye" title="Ver detalle del postulante" />
                                    <!-- Editar Notas Materia -->
                                    <flux:button wire:click="openEditNotas({{ $postulante->id }}, {{ $materia->id }}, '{{ $grupo->nombre }}', '{{ $materia->nombre }}')" size="sm" variant="ghost" icon="pencil-square" title="Editar calificaciones" />
                                    <!-- Eliminar Notas Materia -->
                                    <flux:button wire:click="deleteNotas({{ $postulante->id }}, {{ $materia->id }})" wire:confirm="¿Estás seguro de que deseas restablecer las calificaciones de este estudiante en esta materia?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" title="Restablecer calificaciones" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" class="text-center text-zinc-450 py-12">
                                No se encontraron calificaciones para el criterio de búsqueda.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $calificaciones->links() }}
            </div>
        @else
            <!-- TAB: Configuración de Exámenes (Original Table) -->
            <div class="overflow-x-auto w-full">
                <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nombre</flux:table.column>
                    <flux:table.column>Materia</flux:table.column>
                    <flux:table.column>Gestión</flux:table.column>
                    <flux:table.column>Docente</flux:table.column>
                    <flux:table.column>Alumnos / Postulantes</flux:table.column>
                    <flux:table.column class="text-center">Ponderación</flux:table.column>
                    <flux:table.column>Fecha</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($examenes as $examen)
                        <flux:table.row :key="$examen->id">
                            <flux:table.cell class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $examen->nombre }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $examen->materia?->nombre ?? '—' }}</span>
                                    <span class="text-xs text-zinc-400">{{ $examen->materia?->carrera?->sigla ?? '' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-660 dark:text-zinc-400">{{ $examen->gestion?->nombre ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-650 dark:text-zinc-400">{{ $examen->docentes_names }}</flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-500 dark:text-zinc-400">
                                <div class="max-w-[200px] truncate" title="{{ $examen->alumnos_names }}">
                                    {{ $examen->alumnos_names }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold
                                    {{ $examen->ponderacion >= 50 ? 'bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-900/50' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700' }}">
                                    {{ $examen->ponderacion }}%
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $examen->fecha ? $examen->fecha->format('d/m/Y') : '—' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="openEdit({{ $examen->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                    <flux:button wire:click="delete({{ $examen->id }})" wire:confirm="¿Eliminar este examen?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center text-zinc-450 py-12">
                                No se encontraron exámenes definidos.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

            <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $examenes->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Ver Detalle del Postulante -->
    <flux:modal name="detail-modal" wire:model.self="showDetailModal" class="w-full max-w-3xl">
        @if($selectedPostulante)
            <div class="space-y-6">
                <!-- Header info -->
                <div class="flex items-center gap-4 pb-4 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-2xl shrink-0">
                        {{ mb_substr($selectedPostulante->nombres_apellidos, 0, 1) }}
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedPostulante->nombres_apellidos }}</flux:heading>
                        <p class="text-xs text-zinc-450">CI: {{ $selectedPostulante->ci }} | Gestión: {{ $selectedPostulante->gestion?->nombre ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Datos Personales Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 bg-zinc-100 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Teléfono</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPostulante->telefono ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Colegio de Procedencia</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPostulante->colegio_procedencia ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Ciudad</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPostulante->ciudad ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Sexo</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ $selectedPostulante->sexo === 'M' ? 'Masculino' : ($selectedPostulante->sexo === 'F' ? 'Femenino' : 'Otro') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Fecha Nacimiento</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ $selectedPostulante->fecha_nacimiento ? $selectedPostulante->fecha_nacimiento->format('d/m/Y') : '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Dirección</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate block" title="{{ $selectedPostulante->direccion }}">
                            {{ $selectedPostulante->direccion ?: '—' }}
                        </span>
                    </div>
                </div>

                <!-- Académico Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-zinc-100 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Primera Opción Carrera</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPostulante->carreraPrimeraOpn?->sigla }} - {{ $selectedPostulante->carreraPrimeraOpn?->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Segunda Opción Carrera</span>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPostulante->carreraSegundaOpn ? $selectedPostulante->carreraSegundaOpn->sigla . ' - ' . $selectedPostulante->carreraSegundaOpn->nombre : 'Ninguna' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Promedio Final</span>
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedPostulante->nota_final }} pts</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block mb-0.5">Estado de Admisión</span>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mt-1
                                @if($selectedPostulante->estado_admision === 'admitido_primera_opcion') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                @elseif($selectedPostulante->estado_admision === 'admitido_segunda_opcion') bg-teal-100 text-teal-800 dark:bg-teal-950/50 dark:text-teal-300
                                @elseif($selectedPostulante->estado_admision === 'no_admitido') bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300
                                @elseif($selectedPostulante->estado_admision === 'reprobado') bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-350
                                @else bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-350 @endif">
                                @if($selectedPostulante->estado_admision === 'admitido_primera_opcion') Admitido Primera Opción
                                @elseif($selectedPostulante->estado_admision === 'admitido_segunda_opcion') Admitido Segunda Opción
                                @elseif($selectedPostulante->estado_admision === 'no_admitido') No Admitido (Sin Cupo)
                                @elseif($selectedPostulante->estado_admision === 'reprobado') Reprobado
                                @else Pendiente @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detalle de Notas -->
                <div class="space-y-2.5">
                    <flux:heading size="md" class="font-bold">Calificaciones por Materia</flux:heading>
                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-zinc-50 dark:bg-zinc-950/80 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <th class="px-4 py-3">Materia</th>
                                    <th class="px-4 py-3">Grupo</th>
                                    <th class="px-4 py-3">Docente</th>
                                    <th class="px-4 py-3 text-center">1er Parcial (30%)</th>
                                    <th class="px-4 py-3 text-center">2do Parcial (30%)</th>
                                    <th class="px-4 py-3 text-center">Ex. Final (40%)</th>
                                    <th class="px-4 py-3 text-center">Final</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm">
                                @foreach($postulanteNotas as $notaMat)
                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-950/20 text-zinc-700 dark:text-zinc-300">
                                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $notaMat['materia_nombre'] }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $notaMat['grupo_nombre'] }}</td>
                                        <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">{{ $notaMat['docente_nombre'] }}</td>
                                        <td class="px-4 py-3 text-center font-medium">{{ $notaMat['primer_parcial'] !== null ? round($notaMat['primer_parcial'], 2) : '—' }}</td>
                                        <td class="px-4 py-3 text-center font-medium">{{ $notaMat['segundo_parcial'] !== null ? round($notaMat['segundo_parcial'], 2) : '—' }}</td>
                                        <td class="px-4 py-3 text-center font-medium">{{ $notaMat['examen_final'] !== null ? round($notaMat['examen_final'], 2) : '—' }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ $notaMat['total_materia'] }} pts</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-zinc-150 dark:border-zinc-800">
                    <flux:button wire:click="$set('showDetailModal', false)" variant="ghost">Cerrar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- MODAL: Editar Calificaciones -->
    <flux:modal name="edit-notas-modal" wire:model.self="showEditNotasModal" class="w-full max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Editar Calificaciones</flux:heading>
            @if($selectedPostulante)
                <div class="p-4 bg-zinc-100 dark:bg-zinc-800/50 rounded-xl space-y-1.5 border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $selectedPostulante->nombres_apellidos }}</p>
                    <p class="text-xs text-zinc-400">
                        Materia: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $selectedMateriaNombre }}</span> | 
                        Grupo: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $selectedGrupoName }}</span>
                    </p>
                </div>
            @endif

            <div class="space-y-4 pt-2">
                <flux:field>
                    <flux:label>Nota Primer Parcial (30%)</flux:label>
                    <flux:input wire:model="nota1erParcial" type="number" min="0" max="100" step="0.01" placeholder="Ej: 85.50" />
                    <flux:error name="nota1erParcial" />
                </flux:field>

                <flux:field>
                    <flux:label>Nota Segundo Parcial (30%)</flux:label>
                    <flux:input wire:model="nota2doParcial" type="number" min="0" max="100" step="0.01" placeholder="Ej: 90.00" />
                    <flux:error name="nota2doParcial" />
                </flux:field>

                <flux:field>
                    <flux:label>Nota Examen Final (40%)</flux:label>
                    <flux:input wire:model="nota3erParcial" type="number" min="0" max="100" step="0.01" placeholder="Ej: 75.25" />
                    <flux:error name="nota3erParcial" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-800">
                <flux:button wire:click="$set('showEditNotasModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="saveNotas" variant="primary">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- MODAL: Configuración / Crear / Editar Examen Base -->
    <flux:modal name="examen-modal" wire:model.self="showModal" class="w-full max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Examen' : 'Nuevo Examen' }}</flux:heading>

            <flux:field>
                <flux:label>Nombre del examen</flux:label>
                <flux:select wire:model="nombre">
                    <flux:select.option value="">Seleccionar...</flux:select.option>
                    <flux:select.option value="Primer Parcial">Primer Parcial (30%)</flux:select.option>
                    <flux:select.option value="Segundo Parcial">Segundo Parcial (30%)</flux:select.option>
                    <flux:select.option value="Examen Final">Examen Final (40%)</flux:select.option>
                </flux:select>
                <flux:error name="nombre" />
            </flux:field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Materia</flux:label>
                    <flux:select wire:model="materia_id">
                        <flux:select.option value="">Seleccionar...</flux:select.option>
                        @foreach($materias as $m)
                            <flux:select.option value="{{ $m->id }}">{{ $m->sigla }} - {{ $m->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="materia_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Gestión</flux:label>
                    <flux:select wire:model="gestion_id">
                        <flux:select.option value="">Seleccionar...</flux:select.option>
                        @foreach($gestiones as $g)
                            <flux:select.option value="{{ $g->id }}">{{ $g->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="gestion_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Ponderación (%)</flux:label>
                    <flux:input wire:model="ponderacion" type="number" min="1" max="100" placeholder="Ej: 30" readonly />
                    <flux:error name="ponderacion" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha</flux:label>
                    <flux:input wire:model="fecha" type="date" />
                    <flux:error name="fecha" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear Examen' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
