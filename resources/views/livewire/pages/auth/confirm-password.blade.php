<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Confirmar Acceso</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-6 leading-relaxed">
        Esta es un área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.
    </p>

    <form wire:submit="confirmPassword" class="space-y-5">
        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="password"
                          id="password"
                          class="block mt-1.5 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('Confirmar') }}
            </x-primary-button>
        </div>
    </form>
</div>
