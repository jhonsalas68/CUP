<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <!-- Bell Button -->
    <button @click="open = !open" type="button" class="relative p-2 text-zinc-500 dark:text-zinc-450 hover:bg-zinc-150 dark:hover:bg-zinc-800 rounded-xl transition cursor-pointer select-none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-450 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2.5 w-80 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl z-55 overflow-hidden"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-950/20 border-b border-zinc-150 dark:border-zinc-850 flex justify-between items-center">
            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Notificaciones</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-[10px] font-bold text-indigo-650 hover:underline cursor-pointer">Marcar todo leído</button>
            @endif
        </div>

        <!-- Body -->
        <div class="divide-y divide-zinc-100 dark:divide-zinc-850 max-h-72 overflow-y-auto">
            @forelse($notifications as $notif)
                <div wire:click="markAsRead({{ $notif->id }})" class="p-3.5 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/10 transition cursor-pointer flex gap-2.5 items-start {{ $notif->leido ? 'opacity-65' : 'bg-indigo-50/10 dark:bg-indigo-950/5 font-semibold' }}">
                    <div class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0 {{ $notif->leido ? 'bg-zinc-300' : 'bg-rose-500' }}"></div>
                    <div class="grow space-y-0.5 min-w-0">
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300 truncate pr-2">{{ $notif->titulo }}</span>
                            <span class="text-zinc-400 shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-405 leading-normal truncate-2-lines">{{ $notif->mensaje }}</p>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-zinc-400">No tienes notificaciones.</div>
            @endforelse
        </div>
    </div>
</div>
