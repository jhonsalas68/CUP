<div class="space-y-6">
    <!-- Header principal -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Dashboard Administrativo</flux:heading>
            <flux:subheading>Estadísticas en tiempo real del CUP</flux:subheading>
        </div>
        
        <!-- Selector de Gestión -->
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Semestre:</span>
            <select wire:model.live="selectedGestionId" class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm font-semibold px-4 py-2 text-zinc-800 dark:text-zinc-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                @foreach($gestiones as $g)
                    <option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo) (Activo) @endif</option>
                @endforeach
            </select>
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
                            <flux:table.cell class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $grupo['nombre'] }}</flux:table.cell>
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
</div>
