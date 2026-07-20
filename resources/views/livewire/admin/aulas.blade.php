<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Aulas</flux:heading>
            <flux:subheading>Gestión de aulas físicas y capacidades de la Facultad</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nueva Aula
        </flux:button>
    </div>

    <!-- Alertas de sesión -->
    @if (session()->has('message'))
        <div class="p-4 text-sm text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2 animate-fade-in">
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

    <!-- Buscador y control de voz -->
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-2">
        <div x-data="voiceSearchWidget($wire)" class="relative flex items-center gap-2 grow">
            <div 
                x-show="isListening && currentTranscript" 
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
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre de aula o ubicación..." icon="magnifying-glass" class="w-full" />
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

    <!-- Tabla -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
        <div class="overflow-x-auto w-full">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nombre Aula</flux:table.column>
                    <flux:table.column>Ubicación / Pabellón</flux:table.column>
                    <flux:table.column class="text-center">Capacidad Máxima</flux:table.column>
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($aulas as $aula)
                        <flux:table.row :key="$aula->id">
                            <flux:table.cell class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $aula->nombre }}
                            </flux:table.cell>
                            <flux:table.cell class="font-semibold text-zinc-650 dark:text-zinc-350">
                                {{ $aula->ubicacion ?? 'No especificada' }}
                            </flux:table.cell>
                            <flux:table.cell class="text-center font-bold text-sm text-indigo-600 dark:text-indigo-400">
                                {{ $aula->capacidad }} personas
                            </flux:cell>
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button wire:click="openEdit({{ $aula->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                    <flux:button wire:click="delete({{ $aula->id }})" wire:confirm="¿Seguro que deseas eliminar esta aula? Los grupos asociados perderán su asignación física." size="sm" variant="ghost" icon="trash" class="text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-zinc-400 py-10">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 9h6v3H9V9z" /></svg>
                                    <span>No se encontraron aulas físicas registradas</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $aulas->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <flux:modal name="aula-modal" wire:model.self="showModal" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $isEditing ? 'Editar Aula' : 'Nueva Aula' }}</flux:heading>
            <flux:subheading>Define el nombre identificador y la capacidad máxima para evitar sobrecupos.</flux:subheading>

            <flux:field>
                <flux:label>Nombre del Aula</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej: Aula 101, Laboratorio de Física A" />
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Capacidad Máxima (Personas)</flux:label>
                <flux:input type="number" wire:model="capacidad" placeholder="Ej: 70" />
                <flux:error name="capacidad" />
            </flux:field>

            <flux:field>
                <flux:label>Ubicación / Pabellón (Opcional)</flux:label>
                <flux:input wire:model="ubicacion" placeholder="Ej: Planta Alta - Pabellón 2" />
                <flux:error name="ubicacion" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar Aula' : 'Crear Aula' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
