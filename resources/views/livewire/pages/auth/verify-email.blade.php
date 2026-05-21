<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Verificación de Correo</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-6 leading-relaxed">
        ¡Gracias por registrarte! Por favor, verifica tu dirección de correo electrónico haciendo clic en el enlace que te enviamos. Si no recibiste el correo, con gusto te enviaremos otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs border border-emerald-100 dark:border-emerald-900/50">
            Se ha enviado un nuevo enlace de verificación al correo que proporcionaste.
        </div>
    @endif

    <div class="space-y-4 pt-2">
        <x-primary-button wire:click="sendVerification" class="w-full">
            {{ __('Reenviar Correo de Verificación') }}
        </x-primary-button>

        <button wire:click="logout" type="submit" class="w-full text-center py-2 text-xs font-semibold text-slate-500 hover:text-rose-600 dark:text-zinc-400 dark:hover:text-rose-400 transition-colors">
            {{ __('Cerrar Sesión') }}
        </button>
    </div>
</div>
