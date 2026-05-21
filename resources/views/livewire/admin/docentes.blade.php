<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Docentes</flux:heading>
            <flux:subheading>Gestión del cuerpo docente</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nuevo Docente
        </flux:button>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Búsqueda -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email, CI o especialidad..." icon="magnifying-glass" />
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Docente</flux:table.column>
                <flux:table.column>CI</flux:table.column>
                <flux:table.column>Especialidad</flux:table.column>
                <flux:table.column>Teléfono</flux:table.column>
                <flux:table.column class="text-right">Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($docentes as $docente)
                    <flux:table.row :key="$docente->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($docente->user?->name ?? 'D', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $docente->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ $docente->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">{{ $docente->ci }}</flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">{{ $docente->especialidad ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-400">{{ $docente->telefono ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="openEdit({{ $docente->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                <flux:button wire:click="delete({{ $docente->id }})" wire:confirm="¿Eliminar este docente?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-400 py-10">
                            No se encontraron docentes.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $docentes->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="w-full max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Docente' : 'Nuevo Docente' }}</flux:heading>

            @if(!$isEditing)
                <div class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 rounded-xl border border-amber-100 dark:border-amber-900/50 text-sm">
                    Se creará una cuenta de usuario con contraseña inicial: <strong>password</strong>
                </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field class="sm:col-span-2">
                    <flux:label>Nombre completo</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Juan Pérez" />
                    <flux:error name="name" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="correo@ejemplo.com" />
                    <flux:error name="email" />
                </flux:field>
                <flux:field>
                    <flux:label>CI</flux:label>
                    <flux:input wire:model="ci" placeholder="12345678" />
                    <flux:error name="ci" />
                </flux:field>
                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="telefono" placeholder="70012345" />
                    <flux:error name="telefono" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Especialidad</flux:label>
                    <flux:input wire:model="especialidad" placeholder="Ej: Matemáticas aplicadas" />
                    <flux:error name="especialidad" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>Formación académica</flux:label>
                    <flux:textarea wire:model="formacion_academica" placeholder="Licenciatura en..." rows="3" />
                    <flux:error name="formacion_academica" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear Docente' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
