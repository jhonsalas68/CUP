<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Materias</flux:heading>
            <flux:subheading>Gestión de materias por carrera</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nueva Materia
        </flux:button>
    </div>

    <!-- Panel de Exportación y Reportes Personalizado -->
    <div x-data="{
        tabla: 'materias',
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
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="flex-1 flex items-center gap-2">
            <div x-data="voiceSearchWidget($wire)" class="relative flex items-center gap-2 grow">
                <!-- Floating message (centered above the input) -->
                <div 
                    x-show="isListening || currentTranscript" 
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
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar materia..." icon="magnifying-glass" class="w-full" />
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
        <div class="sm:w-64">
            <flux:select wire:model.live="filterCarrera">
                <flux:select.option value="">Todas las carreras</flux:select.option>
                @foreach($carreras as $carrera)
                    <flux:select.option value="{{ $carrera->id }}">{{ $carrera->sigla }} - {{ $carrera->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <div class="overflow-x-auto w-full">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Sigla</flux:table.column>
                    <flux:table.column>Nombre</flux:table.column>
                    <flux:table.column>Carrera</flux:table.column>
                    <flux:table.column>Docente</flux:table.column>
                    <flux:table.column>Alumnos / Postulantes</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($materias as $materia)
                        <flux:table.row :key="$materia->id">
                            <flux:table.cell>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50">
                                    {{ $materia->sigla }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $materia->nombre }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $materia->carrera?->sigla }} - {{ $materia->carrera?->nombre }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-650 dark:text-zinc-400">
                                {{ $materia->active_gestion_docentes_names }}
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-550 dark:text-zinc-400">
                                <div class="max-w-[200px] truncate" title="{{ $materia->active_gestion_alumnos_names }}">
                                    {{ $materia->active_gestion_alumnos_names }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="openEdit({{ $materia->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                    <flux:button wire:click="delete({{ $materia->id }})" wire:confirm="¿Eliminar esta materia?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-400 py-10">
                                No se encontraron materias.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $materias->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="materia-modal" wire:model.self="showModal" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Materia' : 'Nueva Materia' }}</flux:heading>

            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej: Matemáticas I" />
                <flux:error name="nombre" />
            </flux:field>
            <flux:field>
                <flux:label>Sigla</flux:label>
                <flux:input wire:model="sigla" placeholder="Ej: MAT-101" />
                <flux:error name="sigla" />
            </flux:field>
            <flux:field>
                <flux:label>Carrera</flux:label>
                <flux:select wire:model="carrera_id">
                    <flux:select.option value="">Seleccionar carrera...</flux:select.option>
                    @foreach($carreras as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="carrera_id" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear Materia' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
