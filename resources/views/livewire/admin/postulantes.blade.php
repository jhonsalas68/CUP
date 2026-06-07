<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Postulantes</flux:heading>
            <flux:subheading>Lista de postulantes inscritos al CUP</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nuevo Postulante
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
                <flux:table.column class="text-right">Acciones</flux:table.column>
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
                                <span class="font-bold text-sm {{ $postulante->nota_final >= 60 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
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
                        <flux:table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="openEdit({{ $postulante->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                <flux:button wire:click="delete({{ $postulante->id }})" wire:confirm="¿Eliminar este postulante?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                            </div>
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

    <!-- Modal Crear/Editar -->
    <flux:modal wire:model="showModal" class="w-full max-w-xl">
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

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-150 dark:border-zinc-850">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Inscribir Postulante' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
