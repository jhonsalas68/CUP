<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Recuperar Contraseña</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-6 leading-relaxed">
        ¿Olvidaste tu contraseña? No te preocupes. Escribe tu correo electrónico de registro y te enviaremos un enlace de recuperación.
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus placeholder="ejemplo@cup.edu.bo" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('Enviar Enlace de Recuperación') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-slate-100 dark:border-zinc-800/80 pt-6 text-center">
        <p class="text-xs text-slate-500 dark:text-zinc-400">
            ¿Recordaste tu contraseña? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline" wire:navigate>
                Inicia sesión aquí
            </a>
        </p>
    </div>
</div>
