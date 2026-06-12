<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Grupos</flux:heading>
            <flux:subheading>Gestión de grupos académicos por materia y semestre</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nuevo Grupo
        </flux:button>
    </div>

    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col lg:flex-row gap-3">
        <div class="flex-1 flex items-center gap-3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar grupo, materia o gestión..." icon="magnifying-glass" class="w-full" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
            <flux:select wire:model.live="filterGestion">
                <flux:select.option value="">Todas las gestiones</flux:select.option>
                @foreach($gestiones as $gestion)
                    <flux:select.option value="{{ $gestion->id }}">{{ $gestion->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterCarrera">
                <flux:select.option value="">Todas las carreras</flux:select.option>
                @foreach($carreras as $carrera)
                    <flux:select.option value="{{ $carrera->id }}">{{ $carrera->sigla }} - {{ $carrera->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterMateria">
                <flux:select.option value="">Todas las materias</flux:select.option>
                @foreach($materias as $materia)
                    <flux:select.option value="{{ $materia->id }}">{{ $materia->sigla }} - {{ $materia->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex items-center justify-end gap-3 lg:w-64">
            <button wire:click="limpiarFiltros" type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 px-4 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                Limpiar filtros
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto w-full">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Grupo</flux:table.column>
                    <flux:table.column>Materia</flux:table.column>
                    <flux:table.column>Docente</flux:table.column>
                    <flux:table.column>Carrera</flux:table.column>
                    <flux:table.column>Gestión</flux:table.column>
                    <flux:table.column class="text-center">Cupo</flux:table.column>
                    <flux:table.column class="text-center">Alumnos</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($grupos as $grupo)
                        <flux:table.row :key="$grupo->id">
                            <flux:table.cell>
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $grupo->nombre }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">ID {{ $grupo->id }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $grupo->materia->nombre }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-sm {{ $grupo->docentes->isNotEmpty() ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400 dark:text-zinc-600' }}">
                                    {{ $grupo->docentes->first()?->nombre ?? 'No asignado' }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $grupo->materia->carrera->sigla ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $grupo->docentes->isNotEmpty() ? $grupo->gestion->nombre : $grupo->gestion->nombre }}</flux:table.cell>
                            <flux:table.cell class="text-center">{{ $grupo->cupo_maximo }}</flux:table.cell>
                            <flux:table.cell class="text-center">{{ $grupo->postulantes->count() }}</flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="openEdit({{ $grupo->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                    <flux:button wire:click="delete({{ $grupo->id }})" wire:confirm="¿Eliminar este grupo?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-10 text-zinc-400">No hay grupos registrados. Crea uno usando el botón "Nuevo Grupo".</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <div class="mt-4">
        {{ $grupos->links() }}
    </div>

    <flux:modal name="grupo-modal" wire:model.self="showModal" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ $isEditing ? 'Editar Grupo' : 'Nuevo Grupo' }}</flux:heading>
                    <flux:subheading>Configura el nombre del grupo, materia, gestión y cupo máximo.</flux:subheading>
                </div>
                <flux:button wire:click="$set('showModal', false)" variant="ghost" icon="x-mark" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Nombre del Grupo</flux:label>
                    <flux:input wire:model.defer="nombre" placeholder="Grupo 1" />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>Cupo máximo</flux:label>
                    <flux:input type="number" wire:model.defer="cupo_maximo" min="1" />
                    <flux:error name="cupo_maximo" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Gestión</flux:label>
                    <flux:select wire:model.defer="gestion_id">
                        <flux:select.option value="">Seleccionar gestión</flux:select.option>
                        @foreach($gestiones as $gestion)
                            <flux:select.option value="{{ $gestion->id }}">{{ $gestion->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="gestion_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Carrera / Materia</flux:label>
                    <flux:select wire:model.defer="materia_id">
                        <flux:select.option value="">Seleccionar materia</flux:select.option>
                        @foreach($materias as $materia)
                            <flux:select.option value="{{ $materia->id }}">{{ $materia->sigla }} - {{ $materia->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="materia_id" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Docente Encargado</flux:label>
                <flux:select wire:model.defer="docente_id">
                    <flux:select.option value="">Seleccionar docente (Opcional)</flux:select.option>
                    @foreach($docentesList as $docente)
                        <flux:select.option value="{{ $docente->id }}">{{ $docente->nombre }} ({{ $docente->especialidad ?? 'Sin especialidad' }})</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="docente_id" />
            </flux:field>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">{{ $isEditing ? 'Actualizar' : 'Crear grupo' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
