<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Postulantes</flux:heading>
            <flux:subheading>Lista de postulantes inscritos al CUP</flux:subheading>
        </div>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar nombre, CI..." icon="magnifying-glass" />
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
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Postulante</flux:table.column>
                <flux:table.column>CI</flux:table.column>
                <flux:table.column>1ra Opción</flux:table.column>
                <flux:table.column>2da Opción</flux:table.column>
                <flux:table.column>Nota Final</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column class="text-center">Cambiar Estado</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($postulantes as $postulante)
                    <flux:table.row :key="$postulante->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($postulante->user?->name ?? 'P', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-sm">{{ $postulante->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ $postulante->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">{{ $postulante->ci }}</flux:table.cell>
                        <flux:table.cell>
                            <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300">{{ $postulante->carreraPrimeraOpn?->sigla ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $postulante->carreraSegundaOpn?->sigla ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($postulante->nota_final !== null)
                                <span class="font-bold text-sm {{ $postulante->nota_final >= 51 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format($postulante->nota_final, 2) }}
                                </span>
                            @else
                                <span class="text-zinc-400 text-sm">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $estadoClasses = [
                                    'pendiente'               => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-900/50',
                                    'admitido_primera_opcion' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900/50',
                                    'admitido_segunda_opcion' => 'bg-teal-50 text-teal-700 border-teal-100 dark:bg-teal-950/30 dark:text-teal-300 dark:border-teal-900/50',
                                    'reprobado'               => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-900/50',
                                    'no_presentado'           => 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700',
                                ];
                                $cls = $estadoClasses[$postulante->estado_admision] ?? $estadoClasses['pendiente'];
                                $labels = [
                                    'pendiente' => 'Pendiente',
                                    'admitido_primera_opcion' => 'Admitido 1ra',
                                    'admitido_segunda_opcion' => 'Admitido 2da',
                                    'reprobado' => 'Reprobado',
                                    'no_presentado' => 'No presentado',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $cls }}">
                                {{ $labels[$postulante->estado_admision] ?? $postulante->estado_admision }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            <flux:select wire:change="cambiarEstado({{ $postulante->id }}, $event.target.value)" class="text-xs py-1">
                                @foreach($estadosOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $postulante->estado_admision === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-400 py-10">
                            No se encontraron postulantes con los filtros actuales.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $postulantes->links() }}
        </div>
    </div>
</div>
