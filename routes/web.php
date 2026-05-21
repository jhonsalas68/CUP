<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', function () {
    $user = auth()->user();
    if ($user->hasAnyRole(['Administrador', 'Coordinador'])) {
        return redirect()->route('admin.dashboard');
    }
    // Para otros roles que aún no tienen su vista implementada
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', \App\Livewire\Admin\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::get('admin/carreras', \App\Livewire\Admin\Carreras::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.carreras');

Route::get('admin/materias', \App\Livewire\Admin\Materias::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.materias');

Route::get('admin/docentes', \App\Livewire\Admin\Docentes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.docentes');

Route::get('admin/postulantes', \App\Livewire\Admin\Postulantes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.postulantes');

Route::get('admin/examenes', \App\Livewire\Admin\Examenes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.examenes');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
