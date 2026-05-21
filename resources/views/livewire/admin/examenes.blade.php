<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Exámenes</flux:heading>
            <flux:subheading>Gestión de exámenes y ponderaciones</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nuevo Examen
        </flux:button>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar examen..." icon="magnifying-glass" />
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
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Materia</flux:table.column>
                <flux:table.column>Gestión</flux:table.column>
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
                        <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">{{ $examen->gestion?->nombre ?? '—' }}</flux:table.cell>
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
                        <flux:table.cell colspan="6" class="text-center text-zinc-400 py-10">
                            No se encontraron exámenes.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $examenes->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="w-full max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Examen' : 'Nuevo Examen' }}</flux:heading>

            <flux:field>
                <flux:label>Nombre del examen</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej: Examen Parcial 1" />
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
                    <flux:input wire:model="ponderacion" type="number" min="1" max="100" placeholder="Ej: 30" />
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
