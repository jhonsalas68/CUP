<?php

use App\Http\Controllers\ReportExportController;
use App\Livewire\Admin\Aulas;
use App\Livewire\Admin\Bitacora;
use App\Livewire\Admin\CargaLotes;
use App\Livewire\Admin\Carreras;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Docentes;
use App\Livewire\Admin\Examenes;
use App\Livewire\Admin\Grupos;
use App\Livewire\Admin\Materias;
use App\Livewire\Admin\Postulantes;
use App\Livewire\Admin\VisualizadorAula;
use App\Livewire\CalculadoraAdmision;
use App\Models\Postulante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\Checkout\Session;
use Stripe\Stripe;

Route::view('/', 'welcome');

Route::get('dashboard', function () {
    $user = auth()->user();
    if ($user->hasAnyRole(['Administrador', 'Coordinador'])) {
        return redirect()->route('admin.dashboard');
    }

    // Para otros roles que aún no tienen su vista implementada
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::get('admin/carreras', Carreras::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.carreras');

Route::get('admin/materias', Materias::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.materias');

Route::get('admin/grupos', Grupos::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.grupos');

Route::get('admin/docentes', Docentes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.docentes');

Route::get('admin/postulantes', Postulantes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.postulantes');

Route::get('admin/postulantes/carga', CargaLotes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.carga-lotes');

Route::get('admin/examenes', Examenes::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.examenes');

Route::get('admin/bitacora', Bitacora::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.bitacora');

Route::get('admin/aulas', Aulas::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.aulas');

Route::get('admin/aulas/{aulaId}/visualizador', VisualizadorAula::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.aulas.visualizador');

Route::get('calculadora', CalculadoraAdmision::class)
    ->middleware(['auth', 'verified'])
    ->name('calculadora');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('admin/exportar/postulantes', [ReportExportController::class, 'exportPostulantes'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.postulantes');

Route::get('admin/exportar/admitidos', [ReportExportController::class, 'exportAdmitidos'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.admitidos');

Route::get('admin/exportar/no-admitidos', [ReportExportController::class, 'exportNoAdmitidos'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.no-admitidos');

Route::get('admin/reporte-admision/imprimir', [ReportExportController::class, 'imprimirReporteAdmision'])
    ->middleware(['auth', 'verified'])
    ->name('admin.reporte-admision.imprimir');

Route::get('admin/exportar/personalizado', [ReportExportController::class, 'exportarPersonalizado'])
    ->middleware(['auth', 'verified'])
    ->name('admin.exportar.personalizado');

Route::get('stripe/checkout', function (Request $request) {
    $user = auth()->user();
    $postulante = $user?->postulante;

    if (! $postulante) {
        return redirect()->route('dashboard')->with('error', 'No tienes un perfil de postulante.');
    }

    $type = $request->get('type', 'inscripcion');

    if ($type === 'matricula') {
        if (! in_array($postulante->estado_admision, ['admitido_primera_opcion', 'admitido_segunda_opcion'])) {
            return redirect()->route('dashboard')->with('error', 'No estás admitido en el CUP para realizar este pago.');
        }
        if (! $postulante->pago_realizado) {
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
        Stripe::setApiKey(config('services.stripe.secret'));

        $priceName = $type === 'matricula' ? 'Matrícula de Admisión Académica - CUP' : 'Inscripción Académica - CUP';
        $priceDescription = $type === 'matricula' ? 'Pago de matrícula oficial para postulantes admitidos.' : 'Pago de inscripción oficial para el proceso de admisión CUP.';
        $amount = $type === 'matricula' ? 100000 : 50000; // 1000 BOB or 500 BOB

        $session = Session::create([
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
            'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('dashboard'),
        ]);

        return redirect($session->url);
    } catch (Exception $e) {
        return redirect()->route('dashboard')->with('error', 'Error al conectar con Stripe: '.$e->getMessage());
    }
})->middleware(['auth'])->name('stripe.checkout');

Route::get('stripe/success', function (Request $request) {
    $sessionId = $request->get('session_id');
    if (! $sessionId) {
        return redirect()->route('dashboard')->with('error', 'Falta el ID de sesión de Stripe.');
    }

    try {
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        if ($session && $session->payment_status === 'paid') {
            $postulanteId = $session->metadata->postulante_id ?? null;
            $postulante = Postulante::find($postulanteId);
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
    } catch (Exception $e) {
        return redirect()->route('dashboard')->with('error', 'Error al verificar el pago: '.$e->getMessage());
    }
})->middleware(['auth'])->name('stripe.success');

require __DIR__.'/auth.php';
