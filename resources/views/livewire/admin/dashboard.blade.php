<div class="space-y-6">
    <!-- Header principal -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Dashboard Administrativo</flux:heading>
            <flux:subheading>Estadísticas en tiempo real del CUP</flux:subheading>
        </div>
        
        <!-- Selector de Gestión y Acción -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <!-- Selector de Gestión -->
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Semestre:</span>
                <select wire:model.live="selectedGestionId" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm font-semibold px-4 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    @foreach($gestiones as $g)
                        <option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo) (Activo) @endif</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Botón de Ejecutar Proceso -->
            <button wire:click="openAdmissionProcess" type="button" class="inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
                <span>Procesar Admisiones</span>
            </button>

            <!-- Exportar Reportes -->
            <flux:dropdown>
                <flux:button icon="document-arrow-down" class="cursor-pointer select-none">Exportar</flux:button>
                <flux:menu class="w-56">
                    <flux:menu.item href="{{ route('admin.exportar.postulantes', ['gestion_id' => $selectedGestionId]) }}" icon="users">
                        Exportar Postulantes (CSV)
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('admin.exportar.admitidos', ['gestion_id' => $selectedGestionId]) }}" icon="academic-cap">
                        Exportar Admitidos (CSV)
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <!-- Botón de Notificar por Gmail -->
            @if($totalAdmitidos > 0 || $totalReprobados > 0)
                <button wire:click="sendEmailNotifications" type="button" class="inline-flex items-center justify-center gap-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    <span>Notificar por Gmail</span>
                </button>
            @endif

            <!-- Botón de Enviar Correo de Prueba -->
            <button wire:click="sendTestEmail" type="button" class="inline-flex items-center justify-center gap-2 text-sm font-semibold bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-750 dark:text-zinc-200 px-4 py-2 rounded-xl transition duration-150 shadow-sm border border-zinc-200 dark:border-zinc-700 cursor-pointer select-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                <span>Probar SMTP</span>
            </button>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Postulantes -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Postulantes</span>
                <h3 class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight">{{ $totalPostulantes }}</h3>
                <span class="text-xs text-zinc-400">Registrados en la gestión</span>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
        </div>

        <!-- Admitidos -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Admitidos</span>
                <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $totalAdmitidos }}</h3>
                <span class="text-xs text-zinc-400">
                    @if($totalPostulantes > 0)
                        {{ round(($totalAdmitidos / $totalPostulantes) * 100, 1) }}% de tasa de ingreso
                    @else
                        0% de tasa de ingreso
                    @endif
                </span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
            </div>
        </div>

        <!-- Reprobados -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Reprobados</span>
                <h3 class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 tracking-tight">{{ $totalReprobados }}</h3>
                <span class="text-xs text-zinc-400">
                    @if($totalPostulantes > 0)
                        {{ round(($totalReprobados / $totalPostulantes) * 100, 1) }}% no pasaron las materias
                    @else
                        0%
                    @endif
                </span>
            </div>
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Cupos -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Uso de Cupos</span>
                    <h3 class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight">{{ $totalCuposOcupados }} / {{ $totalCuposDisponibles }}</h3>
                </div>
                <div class="p-2.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 017.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 018.918 5.84 50.45 50.45 0 00-2.658.813m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M12 21V13.5" />
                    </svg>
                </div>
            </div>
            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                @php
                    $percent = $totalCuposDisponibles > 0 ? min(100, ($totalCuposOcupados / $totalCuposDisponibles) * 100) : 0;
                @endphp
                <div class="bg-amber-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
            <span class="text-xs text-zinc-400 block mt-1">{{ round($percent, 1) }}% de cupos cubiertos</span>
        </div>

        <!-- Grupos Habilitados -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Grupos Habilitados</span>
                <h3 class="text-3xl font-extrabold text-indigo-650 dark:text-indigo-400 tracking-tight">{{ $totalGrupos }}</h3>
                <span class="text-xs text-zinc-400">Grupos activos en la gestión</span>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Sección Comparativa de Semestres (Aprobación y Admisión) -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-100 dark:border-zinc-800 pb-4">
            <div>
                <flux:heading size="lg" class="font-bold">Comparativa de Aprobación entre Semestres</flux:heading>
                <flux:subheading>Compara el rendimiento académico y la admisión del semestre seleccionado contra periodos anteriores</flux:subheading>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">Comparar con:</span>
                <select wire:model.live="compareGestionId" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm font-semibold px-4 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                    @foreach($gestiones as $g)
                        @if($g->id != $selectedGestionId)
                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        @if($compareGestionId)
            @php
                $diffPostulantes = $comparePostulantes > 0 ? (($totalPostulantes - $comparePostulantes) / $comparePostulantes) * 100 : 0;
                $diffAprobados = $compareAprobados > 0 ? (($currentAprobados - $compareAprobados) / $compareAprobados) * 100 : 0;
                $diffAdmitidos = $compareAdmitidos > 0 ? (($totalAdmitidos - $compareAdmitidos) / $compareAdmitidos) * 100 : 0;
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna de KPIs Comparativos -->
                <div class="space-y-4 lg:col-span-1 flex flex-col justify-between">
                    <!-- Postulantes -->
                    <div class="bg-zinc-50 dark:bg-zinc-950/20 p-4 rounded-xl border border-zinc-150 dark:border-zinc-850 flex flex-col justify-between">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Postulantes</span>
                            @if($diffPostulantes > 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50 shadow-3xs">
                                    ↑ {{ number_format($diffPostulantes, 1) }}%
                                </span>
                            @elseif($diffPostulantes < 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-250 dark:border-rose-900/50 shadow-3xs">
                                    ↓ {{ number_format(abs($diffPostulantes), 1) }}%
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-zinc-100 text-zinc-650 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shadow-3xs">
                                    = 0%
                                </span>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $totalPostulantes }}</span>
                            <span class="text-xs text-zinc-400">vs {{ $comparePostulantes }} ({{ $compareGestionNombre }})</span>
                        </div>
                    </div>

                    <!-- Aprobados Académicos -->
                    <div class="bg-zinc-50 dark:bg-zinc-950/20 p-4 rounded-xl border border-zinc-150 dark:border-zinc-850 flex flex-col justify-between">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Aprobados Académicos</span>
                            @if($diffAprobados > 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50 shadow-3xs">
                                    ↑ {{ number_format($diffAprobados, 1) }}%
                                </span>
                            @elseif($diffAprobados < 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-250 dark:border-rose-900/50 shadow-3xs">
                                    ↓ {{ number_format(abs($diffAprobados), 1) }}%
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-zinc-100 text-zinc-650 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shadow-3xs">
                                    = 0%
                                </span>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $currentAprobados }}</span>
                            <span class="text-xs text-zinc-400">vs {{ $compareAprobados }} ({{ $compareGestionNombre }})</span>
                        </div>
                    </div>

                    <!-- Admitidos -->
                    <div class="bg-zinc-50 dark:bg-zinc-950/20 p-4 rounded-xl border border-zinc-150 dark:border-zinc-850 flex flex-col justify-between">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Admitidos</span>
                            @if($diffAdmitidos > 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50 shadow-3xs">
                                    ↑ {{ number_format($diffAdmitidos, 1) }}%
                                </span>
                            @elseif($diffAdmitidos < 0)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-250 dark:border-rose-900/50 shadow-3xs">
                                    ↓ {{ number_format(abs($diffAdmitidos), 1) }}%
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-zinc-100 text-zinc-650 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 shadow-3xs">
                                    = 0%
                                </span>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $totalAdmitidos }}</span>
                            <span class="text-xs text-zinc-400">vs {{ $compareAdmitidos }} ({{ $compareGestionNombre }})</span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Comparación Directa -->
                <div class="lg:col-span-2 relative h-72 w-full border border-zinc-150 dark:border-zinc-850 p-4 rounded-xl bg-zinc-50/50 dark:bg-zinc-950/10" wire:ignore x-data="comparisonChartData()" x-init="initChart()">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>
        @else
            <div class="text-center py-6 text-zinc-450 dark:text-zinc-500 bg-zinc-50 dark:bg-zinc-950/50 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800">
                Registra gestiones previas en el sistema para poder visualizar la comparación.
            </div>
        @endif
    </div>

    <!-- Panel de Exportación y Reportes Personalizado -->
    <div x-data="{
        tabla: 'postulantes',
        gestionId: '{{ $selectedGestionId }}',
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
                <a :href="getLink()" target="_blank" class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 h-[38px] rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
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

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:ignore>
        <!-- Gráfico 1: Carreras más demandadas -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs" 
             x-data="demandaChartData()" 
             x-init="initChart()">
            <flux:heading size="lg" class="font-bold mb-4">Carreras más Demandadas</flux:heading>
            <div class="relative h-64 w-full">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Estadísticas Históricas -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs"
             x-data="historicoChartData()"
             x-init="initChart()">
            <flux:heading size="lg" class="font-bold mb-4">Estadísticas Históricas (Postulantes vs Admitidos)</flux:heading>
            <div class="relative h-64 w-full">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Sección: Detalle de Admitidos por Carrera -->
    @if($totalAdmitidos > 0)
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <flux:heading size="lg" class="font-bold">Listado Detallado de Admitidos</flux:heading>
                <flux:subheading>Estudiantes que alcanzaron un cupo en la gestión seleccionada, ordenados por nota final</flux:subheading>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 whitespace-nowrap">Carrera:</span>
                <select wire:model.live="selectedDetailCarreraId" class="w-full sm:w-auto rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm font-semibold px-4 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    @foreach($carrerasList as $c)
                        <option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto w-full border border-zinc-200 dark:border-zinc-800 rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450 dark:text-zinc-400">
                        <th class="py-3 px-4 text-center font-bold w-16">Pos.</th>
                        <th class="py-3 px-4 font-bold">Nombre Completo</th>
                        <th class="py-3 px-4 font-bold">Documento (CI)</th>
                        <th class="py-3 px-4 text-center font-bold">Vía de Ingreso</th>
                        <th class="py-3 px-4 text-right font-bold w-24">Nota Final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-150 dark:divide-zinc-850">
                    @forelse($admitidosDetalle as $adm)
                        <tr class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition duration-150">
                            <td class="py-3 px-4 text-center font-black text-indigo-650 dark:text-indigo-400">#{{ $adm['ranking'] }}</td>
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-white">{{ $adm['nombre'] }}</td>
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $adm['ci'] }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($adm['opcion'] === 1)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        1ra Opción
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/30 dark:text-teal-400 border border-teal-250 dark:border-teal-900/50 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                        2da Opción
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-black text-zinc-900 dark:text-white text-sm">
                                {{ number_format($adm['nota_final'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-zinc-400 dark:text-zinc-500 py-10">
                                No se encontraron estudiantes admitidos para esta carrera en esta gestión.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Tabla: Rendimiento por grupo -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div class="flex justify-between items-center mb-4">
            <div>
                <flux:heading size="lg" class="font-bold">Rendimiento por Grupo</flux:heading>
                <flux:subheading>Promedio de notas y tasas de aprobación por grupo de clase</flux:subheading>
            </div>
        </div>

        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Grupo</flux:table.column>
                    <flux:table.column>Materia</flux:table.column>
                    <flux:table.column>Docente</flux:table.column>
                    <flux:table.column class="text-center">Alumnos</flux:table.column>
                    <flux:table.column class="text-center">Nota Promedio</flux:table.column>
                    <flux:table.column>Tasa Aprobación</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($gruposRendimiento as $grupo)
                        <flux:table.row :key="$grupo['id']">
                            <flux:table.cell>
                                <a wire:click="showGroupDetails({{ $grupo['id'] }})" class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline cursor-pointer select-none">
                                    {{ $grupo['nombre'] }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $grupo['materia'] }}</flux:table.cell>
                            <flux:table.cell>{{ $grupo['docente'] }}</flux:table.cell>
                            <flux:table.cell class="text-center">{{ $grupo['total_alumnos'] }}</flux:table.cell>
                            <flux:table.cell class="text-center font-bold">
                                <span class="{{ $grupo['promedio'] >= 60.00 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($grupo['promedio'], 2) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $grupo['tasa_aprobacion'] >= 60 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $grupo['tasa_aprobacion'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $grupo['tasa_aprobacion'] }}%</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-400 py-8">
                                No se encontraron grupos académicos registrados para esta gestión.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Incluir Chart.js via CDN de manera limpia -->
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endassets

    <!-- Scripts de inicialización y sincronización de gráficos -->
    <script>
        function demandaChartData() {
            return {
                chart: null,
                initChart() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json($carrerasLabels),
                            datasets: [{
                                label: 'Postulantes (Primera Opción)',
                                data: @json($carrerasValues),
                                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                                borderColor: 'rgb(99, 102, 241)',
                                borderRadius: 8,
                                borderWidth: 1,
                                barThickness: 40
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });

                    // Escuchar actualizaciones de Livewire
                    window.addEventListener('stats-updated', () => {
                        this.updateData();
                    });
                },
                updateData() {
                    @this.get('carrerasLabels').then(labels => {
                        this.chart.data.labels = labels;
                        @this.get('carrerasValues').then(values => {
                            this.chart.data.datasets[0].data = values;
                            this.chart.update();
                        });
                    });
                }
            }
        }

        function historicoChartData() {
            return {
                chart: null,
                initChart() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($historicoLabels),
                            datasets: [
                                {
                                    label: 'Postulantes',
                                    data: @json($historicoPostulantes),
                                    borderColor: 'rgb(99, 102, 241)',
                                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                },
                                {
                                    label: 'Admitidos',
                                    data: @json($historicoAdmitidos),
                                    borderColor: 'rgb(16, 185, 129)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                    
                    window.addEventListener('stats-updated', () => {
                        this.updateData();
                    });
                },
                updateData() {
                    @this.get('historicoLabels').then(labels => {
                        this.chart.data.labels = labels;
                        @this.get('historicoPostulantes').then(postulantes => {
                            this.chart.data.datasets[0].data = postulantes;
                            @this.get('historicoAdmitidos').then(admitidos => {
                                this.chart.data.datasets[1].data = admitidos;
                                this.chart.update();
                            });
                        });
                    });
                }
            }
        }

        function comparisonChartData() {
            return {
                chart: null,
                initChart() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Postulantes', 'Aprobados Acad.', 'Admitidos'],
                            datasets: [
                                {
                                    label: 'Semestre Seleccionado',
                                    data: [0, 0, 0],
                                    backgroundColor: 'rgba(99, 102, 241, 0.85)', // Indigo
                                    borderColor: 'rgb(99, 102, 241)',
                                    borderRadius: 6,
                                    borderWidth: 1
                                },
                                {
                                    label: 'Semestre de Comparación',
                                    data: [0, 0, 0],
                                    backgroundColor: 'rgba(148, 163, 184, 0.85)', // Cool Gray / Slate
                                    borderColor: 'rgb(148, 163, 184)',
                                    borderRadius: 6,
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });

                    window.addEventListener('stats-updated', () => {
                        this.updateData();
                    });
                    this.updateData();
                },
                updateData() {
                    @this.get('currentStats').then(current => {
                        this.chart.data.datasets[0].label = current.nombre;
                        this.chart.data.datasets[0].data = [current.postulantes, current.aprobados, current.admitidos];
                        @this.get('compareStats').then(compare => {
                            this.chart.data.datasets[1].label = compare.nombre;
                            this.chart.data.datasets[1].data = [compare.postulantes, compare.aprobados, compare.admitidos];
                            this.chart.update();
                        });
                    });
                }
            }
        }
    </script>

    <!-- Modal de Ejecución de Admisión -->
    @if($showAdmissionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-950/40 dark:bg-zinc-950/60 backdrop-blur-xs transition-opacity" wire:click="$set('showAdmissionModal', false)"></div>

            <!-- Content Container -->
            <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-2xl p-6 md:p-8 animate-fade-in z-10">
                <!-- Accent Bar -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-emerald-500"></div>

                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Proceso de Admisión e Ingreso por Cupos</h3>
                        <p class="text-xs text-zinc-400 mt-1">Evaluación de calificaciones académicas y ranking por cupo</p>
                    </div>
                    <button wire:click="$set('showAdmissionModal', false)" type="button" class="p-1.5 rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                @if($isProcessing)
                    <!-- Processing State -->
                    <div class="flex flex-col items-center justify-center py-12 space-y-4">
                        <svg class="animate-spin h-10 w-10 text-indigo-650" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Procesando rankings y asignación de cupos...</p>
                        <p class="text-xs text-zinc-450">Esto puede tardar unos segundos mientras se evalúa a todos los postulantes.</p>
                    </div>
                @elseif($admissionError)
                    <!-- Error State -->
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-rose-600 dark:text-rose-450 shrink-0"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-rose-800 dark:text-rose-455">No se pudo completar el proceso</h4>
                                <p class="text-xs text-rose-700 dark:text-rose-500 leading-relaxed">{{ $admissionError }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                           <button wire:click="$set('showAdmissionModal', false)" type="button" class="px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm rounded-xl transition duration-150">
                               Cerrar
                           </button>
                        </div>
                    </div>
                @elseif($admissionStats)
                    <!-- Success / Stats State -->
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-600 dark:text-emerald-450 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-400">Proceso completado con éxito</h4>
                                <p class="text-xs text-emerald-700 dark:text-emerald-500">Se han asignado los cupos y reasignado los postulantes a su segunda opción cuando correspondía.</p>
                            </div>
                        </div>

                        <!-- Grid Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-zinc-50 dark:bg-zinc-950/50 p-4 rounded-xl border border-zinc-150 dark:border-zinc-800">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">Postulantes</span>
                                <span class="text-lg font-black text-zinc-800 dark:text-white">{{ $admissionStats['general']['total_postulantes'] }}</span>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-950/50 p-4 rounded-xl border border-zinc-150 dark:border-zinc-800">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">Admitidos</span>
                                <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $admissionStats['general']['total_admitidos'] }}</span>
                                <span class="text-[10px] text-zinc-400 block">{{ $admissionStats['general']['tasa_admision'] }}% de tasa</span>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-950/50 p-4 rounded-xl border border-zinc-150 dark:border-zinc-800">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">Reprobados</span>
                                <span class="text-lg font-black text-rose-600 dark:text-rose-400">{{ $admissionStats['general']['reprobados'] }}</span>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-950/50 p-4 rounded-xl border border-zinc-150 dark:border-zinc-800">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block font-semibold">No Admitidos</span>
                                <span class="text-lg font-black text-amber-600 dark:text-amber-400">{{ $admissionStats['general']['no_admitidos'] }}</span>
                            </div>
                        </div>

                        <!-- Career Stats -->
                        <div class="space-y-3">
                            <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Resumen de Admisión por Carrera</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-zinc-250 dark:border-zinc-850 text-zinc-400">
                                            <th class="py-2">Carrera</th>
                                            <th class="py-2 text-center">Inscritos</th>
                                            <th class="py-2 text-center">Cupos 1ra/2da</th>
                                            <th class="py-2 text-center">Admitidos 1ra/2da</th>
                                            <th class="py-2 text-center">No Admitidos</th>
                                            <th class="py-2 text-right">Nota Corte</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                        @foreach($admissionStats['carreras'] as $sigla => $cStats)
                                            <tr class="text-zinc-700 dark:text-zinc-300">
                                                <td class="py-2.5 font-bold">{{ $sigla }} <span class="text-[10px] font-normal text-zinc-400 block">{{ $cStats['nombre'] }}</span></td>
                                                <td class="py-2.5 text-center">{{ $cStats['inscritos_primera_opcion'] }}</td>
                                                <td class="py-2.5 text-center font-medium">{{ $cStats['cupo_primera_opcion'] }} / {{ $cStats['cupo_segunda_opcion'] }}</td>
                                                <td class="py-2.5 text-center">
                                                    <span class="text-emerald-600 font-bold">{{ $cStats['admitidos_primera_opcion'] }}</span> /
                                                    <span class="text-emerald-500 font-semibold">{{ $cStats['admitidos_segunda_opcion'] }}</span>
                                                </td>
                                                <td class="py-2.5 text-center text-amber-600">{{ $cStats['no_admitidos'] }}</td>
                                                <td class="py-2.5 text-right font-black text-zinc-800 dark:text-white">{{ number_format($cStats['nota_minima_ingreso'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Detailed List of Admitted Students inside modal -->
                        <div class="mt-6 border-t border-zinc-150 dark:border-zinc-800 pt-6 space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Detalle de Alumnos Admitidos</h4>
                                    <p class="text-[10px] text-zinc-450 dark:text-zinc-550">Selecciona una carrera para listar a los postulantes ordenados por nota</p>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Carrera:</span>
                                    <select wire:model.live="selectedDetailCarreraId" class="w-full sm:w-auto rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-xs font-bold px-3 py-1.5 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                        @foreach($carrerasList as $c)
                                            <option value="{{ $c->id }}">{{ $c->sigla }} - {{ $c->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="overflow-x-auto w-full border border-zinc-200 dark:border-zinc-800 rounded-xl max-h-60 overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 dark:bg-zinc-950/50 sticky top-0 border-b border-zinc-200 dark:border-zinc-800 text-zinc-450 dark:text-zinc-400">
                                        <tr>
                                            <th class="py-2 px-3 text-center font-bold w-12">Pos.</th>
                                            <th class="py-2 px-3 font-bold">Nombre Completo</th>
                                            <th class="py-2 px-3 font-bold">CI</th>
                                            <th class="py-2 px-3 text-center font-bold">Opción</th>
                                            <th class="py-2 px-3 text-right font-bold w-20">Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                        @forelse($admitidosDetalle as $adm)
                                            <tr class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20">
                                                <td class="py-2 px-3 text-center font-bold text-indigo-650 dark:text-indigo-400">#{{ $adm['ranking'] }}</td>
                                                <td class="py-2 px-3 font-semibold">{{ $adm['nombre'] }}</td>
                                                <td class="py-2 px-3 text-zinc-500">{{ $adm['ci'] }}</td>
                                                <td class="py-2 px-3 text-center">
                                                    @if($adm['opcion'] === 1)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50">
                                                            1ra Opción
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/30 dark:text-teal-400 border border-teal-250 dark:border-teal-900/50">
                                                            2da Opción
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold text-zinc-900 dark:text-white">{{ number_format($adm['nota_final'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-zinc-400 py-6">
                                                    No se encontraron estudiantes admitidos para esta carrera en esta gestión.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-between items-center gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-800">
                            <!-- Descargas y Reportes -->
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.reporte-admision.imprimir', ['gestion_id' => $selectedGestionId]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821V21m0 0h-5.625c-.621 0-1.125-.504-1.125-1.125V11.25a9 9 0 00-9-9Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821h10.56M6.72 13.821c-.482 0-.964-.138-1.356-.415L1.5 10.5m14.28 3.321h5.625c.621 0 1.125-.504 1.125-1.125v-8.25M6.72 13.821a3.001 3.001 0 01-2.203-1.63L2.25 9m16.5 4.821a3.001 3.001 0 002.203-1.63l1.53-3.19M19.5 9V4.5A1.5 1.5 0 0018 3H6a1.5 1.5 0 00-1.5 1.5V9m15 0H1.5" />
                                    </svg>
                                    <span>Imprimir Reporte (PDF)</span>
                                </a>
                                <a href="{{ route('admin.exportar.admitidos', ['gestion_id' => $selectedGestionId]) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-semibold text-xs rounded-xl transition duration-150 shadow-sm border border-zinc-200 dark:border-zinc-700 cursor-pointer select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Admitidos (CSV)</span>
                                </a>
                                <a href="{{ route('admin.exportar.no-admitidos', ['gestion_id' => $selectedGestionId]) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-semibold text-xs rounded-xl transition duration-150 shadow-sm border border-zinc-200 dark:border-zinc-700 cursor-pointer select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>No Admitidos (CSV)</span>
                                </a>
                                <button type="button" wire:click="sendEmailNotifications" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    <span>Notificar por Gmail</span>
                                </button>
                            </div>

                            <!-- Cerrar Modal -->
                            <button wire:click="$set('showAdmissionModal', false)" type="button" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition duration-155 shadow-sm cursor-pointer select-none">
                                Entendido
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Confirmation Step -->
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50 flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-amber-600 dark:text-amber-450 shrink-0"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400">Atención: Acción Irreversible</h4>
                                <p class="text-xs text-amber-700 dark:text-amber-550 leading-relaxed">
                                    Al presionar "Ejecutar Proceso", se calcularán las notas finales basadas en las ponderaciones, se ordenará a los postulantes aprobados de mayor a menor y se asignarán las plazas disponibles para cada carrera según los cupos de primera y segunda opción.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Requisitos Previos:</h4>
                            <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-2 list-disc pl-5">
                                <li>Todas las materias de la carrera seleccionada deben tener exámenes registrados cuya ponderación total sume el 100%.</li>
                                <li>Todos los exámenes deben tener sus notas completamente registradas. No pueden existir postulantes con notas vacías.</li>
                                <li>Debe haberse configurado los cupos límite para todas las carreras.</li>
                            </ul>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button wire:click="$set('showAdmissionModal', false)" type="button" class="px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm rounded-xl transition duration-150">
                                Cancelar
                            </button>
                            <button wire:click="runAdmissionProcess" type="button" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition duration-150 shadow-sm">
                                Ejecutar Proceso
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal de Detalle de Alumnos de Grupo -->
    @if($showGroupDetailsModal && $selectedGroupInfo)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-950/40 dark:bg-zinc-950/60 backdrop-blur-xs transition-opacity" wire:click="closeGroupDetails"></div>

            <!-- Content Container -->
            <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl w-full max-w-3xl max-h-[85vh] overflow-y-auto shadow-2xl p-6 md:p-8 animate-fade-in z-10">
                <!-- Accent Bar -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-violet-500"></div>

                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Estudiantes del Grupo: {{ $selectedGroupInfo['nombre'] }}</h3>
                        <p class="text-xs text-zinc-400 mt-1">{{ $selectedGroupInfo['materia'] }} &bull; Docente: {{ $selectedGroupInfo['docente'] }}</p>
                    </div>
                    <button wire:click="closeGroupDetails" type="button" class="p-1.5 rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Table Students -->
                <div class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-zinc-250 dark:border-zinc-800 text-zinc-400">
                                    <th class="py-2">Nombre Postulante</th>
                                    <th class="py-2">Documento (CI)</th>
                                    <th class="py-2">Correo Electrónico</th>
                                    <th class="py-2 text-right">Nota Materia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                @forelse($groupAlumnos as $alumno)
                                    <tr class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20">
                                        <td class="py-2.5 font-semibold">{{ $alumno['nombre'] }}</td>
                                        <td class="py-2.5 text-zinc-500">{{ $alumno['ci'] }}</td>
                                        <td class="py-2.5 text-zinc-500">{{ $alumno['email'] }}</td>
                                        <td class="py-2.5 text-right font-bold">
                                            <span class="{{ $alumno['nota_materia'] >= 60.00 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ number_format($alumno['nota_materia'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-zinc-400 py-10">
                                            No existen alumnos asignados a este grupo académico.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-zinc-150 dark:border-zinc-850 mt-6">
                    <button wire:click="closeGroupDetails" type="button" class="px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm rounded-xl transition duration-150">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
