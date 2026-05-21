<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Restablecer Contraseña</h2>
    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1 mb-8">Ingresa tu nueva contraseña para actualizar tu acceso.</p>

    <form wire:submit="resetPassword" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Nueva Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" class="font-bold text-xs text-slate-700 dark:text-zinc-300" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password" placeholder="Confirmar nueva contraseña" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-600 dark:text-rose-400" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('Restablecer Contraseña') }}
            </x-primary-button>
        </div>
    </form>
</div>
