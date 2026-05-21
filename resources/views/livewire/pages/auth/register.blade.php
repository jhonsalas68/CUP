<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Registro de Postulante</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-8">Completa tus datos para crear tu cuenta en el sistema CUP.</p>

    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="name" id="name" class="block mt-1.5 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="Juan Pérez" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autocomplete="username" placeholder="juan.perez@ejemplo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Confirmar contraseña" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('Crear Cuenta') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-slate-100 dark:border-zinc-800/80 pt-6 text-center">
        <p class="text-xs text-slate-500 dark:text-zinc-400">
            ¿Ya tienes una cuenta registrada? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline" wire:navigate>
                Inicia sesión aquí
            </a>
        </p>
    </div>
</div>
