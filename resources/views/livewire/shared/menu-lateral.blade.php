<div class="flex flex-col h-[calc(100vh-8rem)] justify-between">
    <!-- Lista de Navegación -->
    <flux:navlist class="space-y-1.5">
        @if(auth()->user()->hasAnyRole(['Administrador', 'Coordinador']))
            <flux:navlist.item icon="squares-2x2" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')">
                Dashboard
            </flux:navlist.item>

            <flux:navlist.item icon="academic-cap" href="{{ route('admin.carreras') }}" :current="request()->routeIs('admin.carreras')">
                Carreras
            </flux:navlist.item>

            <flux:navlist.item icon="book-open" href="{{ route('admin.materias') }}" :current="request()->routeIs('admin.materias')">
                Materias
            </flux:navlist.item>

            <flux:navlist.item icon="rectangle-stack" href="{{ route('admin.grupos') }}" :current="request()->routeIs('admin.grupos')">
                Grupos
            </flux:navlist.item>

            <flux:navlist.item icon="users" href="{{ route('admin.docentes') }}" :current="request()->routeIs('admin.docentes')">
                Docentes
            </flux:navlist.item>

            <flux:navlist.item icon="identification" href="{{ route('admin.postulantes') }}" :current="request()->routeIs('admin.postulantes')">
                Postulantes
            </flux:navlist.item>

            <flux:navlist.item icon="document-arrow-up" href="{{ route('admin.carga-lotes') }}" :current="request()->routeIs('admin.carga-lotes')">
                Carga Masiva (CSV)
            </flux:navlist.item>

            <flux:navlist.item icon="clipboard-document-check" href="{{ route('admin.examenes') }}" :current="request()->routeIs('admin.examenes')">
                Exámenes
            </flux:navlist.item>

            <flux:navlist.item icon="home" href="{{ route('admin.aulas') }}" :current="request()->routeIs('admin.aulas')">
                Aulas
            </flux:navlist.item>

            <flux:navlist.item icon="calculator" href="{{ route('calculadora') }}" :current="request()->routeIs('calculadora')">
                Calculadora Admisión
            </flux:navlist.item>

            @if(auth()->user()->hasRole('Administrador'))
                <flux:navlist.item icon="command-line" href="{{ route('admin.bitacora') }}" :current="request()->routeIs('admin.bitacora')">
                    Bitácora
                </flux:navlist.item>
            @endif

        @elseif(auth()->user()->hasRole('Docente'))
            <flux:navlist.item icon="squares-2x2" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">
                Mi Dashboard
            </flux:navlist.item>
            <flux:navlist.item icon="calculator" href="{{ route('calculadora') }}" :current="request()->routeIs('calculadora')">
                Calculadora Admisión
            </flux:navlist.item>
        @elseif(auth()->user()->hasRole('Postulante'))
            <flux:navlist.item icon="squares-2x2" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">
                Mis Resultados
            </flux:navlist.item>
            <flux:navlist.item icon="calculator" href="{{ route('calculadora') }}" :current="request()->routeIs('calculadora')">
                Calculadora Admisión
            </flux:navlist.item>
        @endif
    </flux:navlist>

    <!-- Botón de Logout oculto accesible desde el Header/Layout -->
    <button wire:click="logout" id="logout-btn-sidebar" class="hidden"></button>

    <!-- Footer del Sidebar con Perfil y Logout -->
    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-4 mt-auto">
        <div class="flex items-center justify-between gap-2 bg-zinc-50 dark:bg-zinc-800/40 p-2.5 rounded-2xl border border-zinc-100 dark:border-zinc-800/80">
            <div class="flex items-center gap-2.5 min-w-0">
                <flux:avatar class="w-8 h-8 rounded-xl border border-zinc-200 dark:border-zinc-700 shrink-0" name="{{ auth()->user()->name }}" />
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-zinc-400 truncate mt-0.5 leading-none">{{ auth()->user()->email }}</span>
                </div>
            </div>
            
            <!-- Botón de Cerrar Sesión Directo -->
            <button wire:click="logout" title="Cerrar Sesión" class="p-1.5 text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
            </button>
        </div>
    </div>
</div>
