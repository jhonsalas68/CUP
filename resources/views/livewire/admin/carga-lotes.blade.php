<div class="space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
        <div>
            <flux:heading size="xl" class="font-bold tracking-tight">Carga Masiva de Postulantes</flux:heading>
            <flux:subheading>Importar cuentas y perfiles de alumnos en lotes a partir de un archivo CSV</flux:subheading>
        </div>
        
        <flux:button href="{{ route('admin.postulantes') }}" icon="arrow-left" variant="ghost" class="cursor-pointer select-none">
            Volver a Postulantes
        </flux:button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario de Carga -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-850 pb-4">
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">Subir Archivo</h3>
                <button wire:click="downloadTemplate" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Descargar Plantilla CSV</span>
                </button>
            </div>

            <form wire:submit="procesar" class="space-y-6">
                <!-- Selector de Gestión Destino -->
                <flux:field>
                    <flux:label class="font-semibold text-zinc-800 dark:text-zinc-200">Gestión Académica de Inscripción</flux:label>
                    <flux:select wire:model="selectedGestionId" class="max-w-md">
                        <option value="">Seleccione una gestión...</option>
                        @foreach($gestiones as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }} @if($g->activo)(Activa)@endif</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="selectedGestionId" />
                </flux:field>

                <!-- Área de Carga de Archivo -->
                <div class="space-y-2">
                    <flux:label class="font-semibold text-zinc-800 dark:text-zinc-200">Archivo CSV (.csv)</flux:label>
                    
                    <div 
                        x-data="{ isDropping: false }"
                        @dragover.prevent="isDropping = true"
                        @dragleave.prevent="isDropping = false"
                        @drop.prevent="isDropping = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                        :class="isDropping ? 'border-indigo-550 bg-indigo-50/20 dark:bg-indigo-950/10' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-350 dark:hover:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950/20'"
                        class="relative border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center transition-all duration-200 cursor-pointer min-h-[220px]"
                    >
                        <!-- Campo input oculto -->
                        <input 
                            x-ref="fileInput"
                            type="file" 
                            wire:model="file" 
                            accept=".csv,text/csv,text/plain"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />

                        @if($file)
                            <!-- Archivo Seleccionado -->
                            <div class="space-y-3">
                                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-2xl inline-flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-800 dark:text-white truncate max-w-xs">{{ $file->getClientOriginalName() }}</p>
                                    <p class="text-xs text-zinc-400 mt-1">{{ round($file->getSize() / 1024, 1) }} KB</p>
                                </div>
                                <button type="button" wire:click="$set('file', null)" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:underline">
                                    Quitar archivo
                                </button>
                            </div>
                        @else
                            <!-- Drag and Drop Prompts -->
                            <div class="space-y-3">
                                <div class="p-3 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-550 dark:text-zinc-400 rounded-2xl inline-flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-200">
                                        Arrastra y suelta tu archivo CSV aquí, o <span class="text-indigo-600 dark:text-indigo-400">haz clic para explorar</span>
                                    </p>
                                    <p class="text-xs text-zinc-400 mt-1.5">Solo archivos CSV (.csv) hasta 4MB</p>
                                </div>
                            </div>
                        @endif

                        <!-- Livewire Uploading Indicator -->
                        <div wire:uploading.flex style="display: none;" class="absolute inset-0 bg-white/90 dark:bg-zinc-900/90 rounded-2xl flex flex-col items-center justify-center p-4">
                            <svg class="animate-spin h-7 w-7 text-indigo-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">Subiendo archivo...</p>
                        </div>
                    </div>
                    <flux:error name="file" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-850">
                    <flux:button href="{{ route('admin.postulantes') }}" variant="ghost">
                        Cancelar
                    </flux:button>
                    
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        @if(!$file) disabled @endif
                        class="inline-flex items-center justify-center gap-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:hover:bg-indigo-600 text-white px-5 py-2.5 rounded-xl transition duration-150 shadow-sm cursor-pointer select-none"
                    >
                        <span wire:loading.remove wire:target="procesar">Importar Postulantes</span>
                        <span wire:loading wire:target="procesar" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Procesando lote...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Instrucciones y Guías -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-5">
            <h3 class="text-base font-bold text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-850 pb-3">Instrucciones del Formato</h3>
            
            <div class="space-y-4 text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                <p>
                    Para realizar una carga correcta, asegúrate de que el archivo CSV posea la cabecera correspondiente. Puedes descargar nuestra plantilla como guía.
                </p>

                <div class="space-y-2">
                    <h4 class="font-bold text-zinc-700 dark:text-zinc-300">Campos Obligatorios:</h4>
                    <ul class="list-disc pl-4 space-y-1">
                        <li><strong>nombre</strong>: Nombre y apellido.</li>
                        <li><strong>email</strong>: Correo único (sin repetir en BD ni en archivo).</li>
                        <li><strong>ci</strong>: Documento de identidad único.</li>
                        <li><strong>telefono</strong>: Teléfono de contacto.</li>
                        <li><strong>fecha_nacimiento</strong>: Formato `AAAA-MM-DD`.</li>
                        <li><strong>sexo</strong>: Letra `M` o `F`.</li>
                        <li><strong>direccion</strong>: Dirección física.</li>
                        <li><strong>colegio</strong>: Colegio de bachillerato.</li>
                        <li><strong>ciudad</strong>: Ciudad de origen.</li>
                        <li><strong>carrera_1ra</strong>: Sigla registrada (ej: `SIS`, `INF`).</li>
                    </ul>
                </div>

                <div class="space-y-1 bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 p-3.5 rounded-xl text-amber-600 dark:text-amber-400">
                    <h4 class="font-bold text-[11px] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
                        Transacción Segura
                    </h4>
                    <p class="text-[11px]">
                        Si una sola fila falla (ej. correo duplicado), toda la importación se cancela de manera segura, evitando duplicidades parciales.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados del Procesamiento -->
    @if($isProcessed)
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4 animate-fade-in">
            <h3 class="text-base font-bold text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-850 pb-3">Resultados del Procesamiento</h3>

            @if(empty($errorsList))
                <!-- Exito Completo -->
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-150 dark:border-emerald-900/50 rounded-2xl flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-600 dark:text-emerald-450 shrink-0"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.748-5.25z" clip-rule="evenodd" /></svg>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-400">Importación Finalizada con Éxito</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-500">Se registraron exitosamente <strong>{{ $successCount }}</strong> nuevos postulantes y se crearon sus respectivas credenciales de usuario.</p>
                    </div>
                </div>
            @else
                <!-- Lista de Errores -->
                <div class="space-y-4">
                    <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-150 dark:border-rose-900/50 rounded-2xl flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-rose-600 dark:text-rose-450 shrink-0"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-rose-800 dark:text-rose-400">Se detectaron errores en el archivo CSV</h4>
                            <p class="text-xs text-rose-700 dark:text-rose-500">Se canceló el procesamiento completo para evitar registros incompletos. Por favor corrige los siguientes puntos y vuelve a subir el archivo:</p>
                        </div>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-850 p-4 max-h-64 overflow-y-auto">
                        <ul class="space-y-2 text-xs font-mono text-rose-650 dark:text-rose-400">
                            @foreach($errorsList as $err)
                                <li class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></span>
                                    <span>{{ $err }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
