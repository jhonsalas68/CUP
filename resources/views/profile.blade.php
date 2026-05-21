<x-admin-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="space-y-1 mb-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">Mi Perfil Académico</h2>
            <p class="text-xs text-zinc-400">Actualiza la información de tu cuenta universitaria y configuraciones de seguridad.</p>
        </div>

        <div class="p-6 sm:p-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-admin-layout>
