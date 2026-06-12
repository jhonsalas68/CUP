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

Route::get('admin/grupos', \App\Livewire\Admin\Grupos::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.grupos');

Route::get('admin/docentes', \App\Livewire\Admin\Docentes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.docentes');

Route::get('admin/postulantes', \App\Livewire\Admin\Postulantes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.postulantes');

Route::get('admin/postulantes/carga', \App\Livewire\Admin\CargaLotes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.carga-lotes');

Route::get('admin/examenes', \App\Livewire\Admin\Examenes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.examenes');



Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('admin/exportar/postulantes', [\App\Http\Controllers\ReportExportController::class, 'exportPostulantes'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.postulantes');

Route::get('admin/exportar/admitidos', [\App\Http\Controllers\ReportExportController::class, 'exportAdmitidos'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.admitidos');

Route::get('admin/exportar/no-admitidos', [\App\Http\Controllers\ReportExportController::class, 'exportNoAdmitidos'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.no-admitidos');

Route::get('admin/reporte-admision/imprimir', [\App\Http\Controllers\ReportExportController::class, 'imprimirReporteAdmision'])
    ->middleware(['auth', 'verified'])
    ->name('admin.reporte-admision.imprimir');

Route::get('admin/exportar/personalizado', [\App\Http\Controllers\ReportExportController::class, 'exportarPersonalizado'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.personalizado');

Route::get('stripe/checkout', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $postulante = $user?->postulante;
    
    if (!$postulante) {
        return redirect()->route('dashboard')->with('error', 'No tienes un perfil de postulante.');
    }
    
    $type = $request->get('type', 'inscripcion');
    
    if ($type === 'matricula') {
        if (!in_array($postulante->estado_admision, ['admitido_primera_opcion', 'admitido_segunda_opcion'])) {
            return redirect()->route('dashboard')->with('error', 'No estás admitido en el CUP para realizar este pago.');
        }
        if (!$postulante->pago_realizado) {
            return redirect()->route('dashboard')->with('error', 'Debes realizar el pago de inscripción antes de pagar la matrícula.');
        }
        if ($postulante->pago_matricula_realizado) {
            return redirect()->route('dashboard')->with('message', 'Tu matrícula ya ha sido pagada.');
        }
    } else {
        if ($postulante->pago_realizado) {
            return redirect()->route('dashboard')->with('message', 'Tu inscripción ya ha sido pagada.');
        }
    }

    if (app()->environment('testing')) {
        if ($type === 'matricula') {
            $postulante->update(['pago_matricula_realizado' => true]);
            return redirect()->route('dashboard')->with('success_message', '¡Matrícula pagada con éxito! (Simulado en Testing)');
        } else {
            $postulante->update(['pago_realizado' => true]);
            return redirect()->route('dashboard')->with('success_message', '¡Inscripción pagada con éxito! (Simulado en Testing)');
        }
    }

    try {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        
        $priceName = $type === 'matricula' ? 'Matrícula de Admisión Académica - CUP' : 'Inscripción Académica - CUP';
        $priceDescription = $type === 'matricula' ? 'Pago de matrícula oficial para postulantes admitidos.' : 'Pago de inscripción oficial para el proceso de admisión CUP.';
        $amount = $type === 'matricula' ? 100000 : 50000; // 1000 BOB or 500 BOB
        
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'bob',
                    'product_data' => [
                        'name' => $priceName,
                        'description' => $priceDescription,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'type' => $type,
                'postulante_id' => $postulante->id,
            ],
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('dashboard'),
        ]);

        return redirect($session->url);
    } catch (\Exception $e) {
        return redirect()->route('dashboard')->with('error', 'Error al conectar con Stripe: ' . $e->getMessage());
    }
})->middleware(['auth'])->name('stripe.checkout');

Route::get('stripe/success', function (\Illuminate\Http\Request $request) {
    $sessionId = $request->get('session_id');
    if (!$sessionId) {
        return redirect()->route('dashboard')->with('error', 'Falta el ID de sesión de Stripe.');
    }

    try {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if ($session && $session->payment_status === 'paid') {
            $postulanteId = $session->metadata->postulante_id ?? null;
            $postulante = \App\Models\Postulante::find($postulanteId);
            if ($postulante) {
                $type = $session->metadata->type ?? 'inscripcion';
                if ($type === 'matricula') {
                    $postulante->update(['pago_matricula_realizado' => true]);
                    return redirect()->route('dashboard')->with('success_message', '¡Matrícula pagada con éxito!');
                } else {
                    $postulante->update(['pago_realizado' => true]);
                    return redirect()->route('dashboard')->with('success_message', '¡Inscripción pagada con éxito!');
                }
            }
        }

        return redirect()->route('dashboard')->with('error', 'El pago no pudo ser verificado.');
    } catch (\Exception $e) {
        return redirect()->route('dashboard')->with('error', 'Error al verificar el pago: ' . $e->getMessage());
    }
})->middleware(['auth'])->name('stripe.success');

require __DIR__.'/auth.php';
