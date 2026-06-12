<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();
        if ($user && $user->hasAnyRole(['Administrador', 'Coordinador'])) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Acceso Académico</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-8">Ingresa tus credenciales para acceder a la plataforma.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="ejemplo@cup.edu.bo" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center">
                <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <x-text-input wire:model="form.password" id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" name="remember">
                <span class="ms-2 text-xs font-semibold text-slate-500 dark:text-zinc-400">{{ __('Recordarme en este equipo') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('Iniciar Sesión') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 border-t border-slate-100 dark:border-zinc-800/80 pt-6 text-center">
            <p class="text-xs text-slate-500 dark:text-zinc-400">
                ¿Aún no te has registrado? 
                <a href="{{ route('register') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline" wire:navigate>
                    Crea tu cuenta de postulante
                </a>
            </p>
        </div>
    @endif
</div>
