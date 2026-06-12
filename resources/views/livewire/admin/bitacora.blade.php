<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Bitácora de Actividades</flux:heading>
            <flux:subheading>Registro de auditoría del sistema e historial de acciones de administración</flux:subheading>
        </div>
        <flux:button wire:click="clearLogs" wire:confirm="¿Estás seguro de que deseas vaciar todos los registros de la bitácora de actividades? Esta acción no se puede deshacer." variant="danger" icon="trash" class="cursor-pointer">
            Vaciar Bitácora
        </flux:button>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="grow w-full">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por descripción, objeto, dirección IP u operador..." icon="magnifying-glass" class="w-full" />
            </div>
            <div class="w-full md:w-64">
                <flux:select wire:model.live="selectedAction" placeholder="Filtrar por acción...">
                    <flux:select.option value="">Todas las acciones</flux:select.option>
                    <flux:select.option value="crear">Creación</flux:select.option>
                    <flux:select.option value="actualizar">Actualización / Edición</flux:select.option>
                    <flux:select.option value="eliminar">Eliminación</flux:select.option>
                    <flux:select.option value="proceso_admision">Proceso de Admisión</flux:select.option>
                    <flux:select.option value="login">Inicio de Sesión</flux:select.option>
                    <flux:select.option value="logout">Cierre de Sesión</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/60 dark:bg-zinc-900/60 items-center justify-center z-10 rounded-2xl">
            <svg class="animate-spin w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
        
        <div class="overflow-x-auto w-full">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Fecha y Hora</flux:table.column>
                    <flux:table.column>Operador</flux:table.column>
                    <flux:table.column>Acción</flux:table.column>
                    <flux:table.column>Objeto Afectado</flux:table.column>
                    <flux:table.column>Descripción</flux:table.column>
                    <flux:table.column>IP Address</flux:table.column>
                    <flux:table.column class="text-right">Detalle</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @forelse($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap font-medium text-zinc-550 dark:text-zinc-400">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                @if($log->user)
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $log->user->name }}</div>
                                    <div class="text-[10px] text-zinc-450 dark:text-zinc-500 font-normal">{{ $log->user->email }}</div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                        Sistema
                                    </span>
                                @endif
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                @if($log->action === 'crear')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50">
                                        Creación
                                    </span>
                                @elseif($log->action === 'actualizar')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900/50">
                                        Modificación
                                    </span>
                                @elseif($log->action === 'eliminar')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-100 dark:border-rose-900/50">
                                        Eliminación
                                    </span>
                                @elseif($log->action === 'proceso_admision')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-900/50">
                                        Admisión
                                    </span>
                                @elseif($log->action === 'login')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900/50">
                                        Login
                                    </span>
                                @elseif($log->action === 'logout')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-355 border border-zinc-200 dark:border-zinc-700">
                                        Logout
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-50 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-850">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </flux:table.cell>
                            
                            <flux:table.cell class="font-semibold text-zinc-800 dark:text-zinc-200 whitespace-nowrap">
                                {{ $log->objeto }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="max-w-xs truncate text-zinc-650 dark:text-zinc-400" title="{{ $log->descripcion }}">
                                {{ $log->descripcion }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="font-mono text-xs text-zinc-550 dark:text-zinc-450">
                                {{ $log->ip_address ?? 'N/A' }}
                            </flux:table.cell>
                            
                            <flux:table.cell class="text-right">
                                <flux:button wire:click="showDetail({{ $log->id }})" size="sm" variant="ghost" icon="eye" class="cursor-pointer" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center text-zinc-400 py-10">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <span>No se encontraron registros de actividades.</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Modal Detalle -->
    <flux:modal name="detail-modal" wire:model.self="showDetailModal" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detalle del Registro de Auditoría</flux:heading>
                <flux:subheading>Inspección de datos técnicos y cambios realizados en la base de datos</flux:subheading>
            </div>

            @if($selectedLog)
                <!-- Datos generales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs bg-zinc-50 dark:bg-zinc-950 p-4 rounded-xl border border-zinc-150 dark:border-zinc-850">
                    <div>
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Fecha y Hora:</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $selectedLog->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Dirección IP:</span>
                        <span class="font-mono text-zinc-800 dark:text-zinc-200">{{ $selectedLog->ip_address ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Operador:</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">
                            @if($selectedLog->user)
                                {{ $selectedLog->user->name }} ({{ $selectedLog->user->email }})
                            @else
                                Sistema (Acción automatizada o no autenticada)
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Acción realizada:</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200 capitalize font-mono">{{ $selectedLog->action }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Objeto afectado:</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $selectedLog->objeto }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-semibold text-zinc-400 mb-0.5">Descripción completa:</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $selectedLog->descripcion }}</span>
                    </div>
                </div>

                <!-- Comparación de actualización -->
                @if($selectedLog->action === 'actualizar' && isset($selectedLog->payload['dirty']) && isset($selectedLog->payload['original']))
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-zinc-550 dark:text-zinc-400 block">Comparativa de Atributos Modificados:</span>
                        <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-150 dark:border-zinc-850 font-bold text-zinc-600 dark:text-zinc-450">
                                        <th class="p-3">Atributo</th>
                                        <th class="p-3">Valor Anterior (Original)</th>
                                        <th class="p-3">Valor Nuevo (Modificado)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                    @foreach($selectedLog->payload['dirty'] as $key => $newValue)
                                        @php
                                            $oldValue = $selectedLog->payload['original'][$key] ?? null;
                                            $oldStr = is_bool($oldValue) ? ($oldValue ? 'true' : 'false') : (is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : $oldValue);
                                            $newStr = is_bool($newValue) ? ($newValue ? 'true' : 'false') : (is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : $newValue);
                                        @endphp
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-950/20">
                                            <td class="p-3 font-mono font-bold text-zinc-600 dark:text-zinc-400">{{ $key }}</td>
                                            <td class="p-3 bg-rose-50/30 dark:bg-rose-950/10 text-rose-700 dark:text-rose-400 font-mono line-through whitespace-pre-wrap">{{ $oldStr !== null && $oldStr !== '' ? $oldStr : '(vacío)' }}</td>
                                            <td class="p-3 bg-emerald-50/30 dark:bg-emerald-950/10 text-emerald-700 dark:text-emerald-400 font-mono font-semibold whitespace-pre-wrap">{{ $newStr !== null && $newStr !== '' ? $newStr : '(vacío)' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Datos de Creación -->
                @if($selectedLog->action === 'crear' && is_array($selectedLog->payload))
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-zinc-550 dark:text-zinc-400 block">Datos del Registro Creado:</span>
                        <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-150 dark:border-zinc-850 font-bold text-zinc-600 dark:text-zinc-450">
                                        <th class="p-3">Atributo</th>
                                        <th class="p-3">Valor Registrado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-850">
                                    @foreach($selectedLog->payload as $key => $value)
                                        @php
                                            $valStr = is_bool($value) ? ($value ? 'true' : 'false') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value);
                                        @endphp
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-950/20">
                                            <td class="p-3 font-mono font-bold text-zinc-600 dark:text-zinc-400">{{ $key }}</td>
                                            <td class="p-3 text-zinc-800 dark:text-zinc-200 font-mono whitespace-pre-wrap">{{ $valStr !== null && $valStr !== '' ? $valStr : '(vacío)' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Estadísticas del Proceso de Admisión -->
                @if($selectedLog->action === 'proceso_admision' && isset($selectedLog->payload['stats']))
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-zinc-550 dark:text-zinc-400 block">Resumen del Proceso de Admisión:</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($selectedLog->payload['stats'] as $metric => $val)
                                @php
                                    $metricLabel = str_replace('_', ' ', $metric);
                                @endphp
                                <div class="bg-zinc-50 dark:bg-zinc-950 p-3 rounded-xl border border-zinc-150 dark:border-zinc-850 text-center">
                                    <span class="block text-[10px] uppercase font-bold text-zinc-450 dark:text-zinc-500 tracking-wider mb-1">{{ $metricLabel }}</span>
                                    <span class="text-base font-extrabold text-indigo-600 dark:text-indigo-400">{{ $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="closeDetail" variant="ghost">Cerrar Detalle</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
