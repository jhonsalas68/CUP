<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Carreras</flux:heading>
            <flux:subheading>Gestión de carreras universitarias</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nueva Carrera
        </flux:button>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Barra de búsqueda -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o sigla..." icon="magnifying-glass" />
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Sigla</flux:table.column>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column class="text-center">Materias</flux:table.column>
                <flux:table.column class="text-right">Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($carreras as $carrera)
                    <flux:table.row :key="$carrera->id">
                        <flux:table.cell>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                {{ $carrera->sigla }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $carrera->nombre }}</flux:table.cell>
                        <flux:table.cell class="text-center">
                            <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">{{ $carrera->materias_count }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="openEdit({{ $carrera->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                <flux:button wire:click="delete({{ $carrera->id }})" wire:confirm="¿Eliminar esta carrera?" size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-400 py-10">
                            <div class="flex flex-col items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 017.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 018.918 5.84 50.45 50.45 0 00-2.658.813m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M12 21V13.5" /></svg>
                                <span>No se encontraron carreras</span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $carreras->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <flux:modal wire:model="showModal" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Carrera' : 'Nueva Carrera' }}</flux:heading>

            <flux:field>
                <flux:label>Nombre completo</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej: Ingeniería de Sistemas" />
                <flux:error name="nombre" />
            </flux:field>
            <flux:field>
                <flux:label>Sigla</flux:label>
                <flux:input wire:model="sigla" placeholder="Ej: INF" />
                <flux:error name="sigla" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear Carrera' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
