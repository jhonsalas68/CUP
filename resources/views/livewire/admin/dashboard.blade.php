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
            <button wire:click="openAdmissionProcess" type="button" class="inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-655 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition duration-150 shadow-sm cursor-pointer select-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
                <span>Procesar Admisiones</span>
            </button>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

                        <div class="flex justify-end gap-3 pt-2">
                            <button wire:click="$set('showAdmissionModal', false)" type="button" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition duration-155 shadow-sm">
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
