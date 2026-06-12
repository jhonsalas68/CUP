<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Admisión CUP</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f4f4f5;
            color: #18181b;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f4f5;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px border #e4e4e7;
        }
        .header {
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header.admitted {
            background: linear-gradient(135deg, #4f46e5 0%, #10b981 100%);
        }
        .header.other {
            background: linear-gradient(135deg, #1f2937 0%, #4b5563 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #09090b;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #3f3f46;
            margin-bottom: 24px;
        }
        .card {
            background-color: #fafafa;
            border: 1px solid #f4f4f5;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .card-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            color: #71717a;
            margin-bottom: 12px;
            letter-spacing: 0.05em;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            color: #71717a;
        }
        .info-value {
            font-weight: 600;
            color: #18181b;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge.emerald {
            background-color: #ecfdf5;
            color: #047857;
        }
        .badge.teal {
            background-color: #f0fdfa;
            color: #0f766e;
        }
        .badge.rose {
            background-color: #fff1f2;
            color: #be123c;
        }
        .badge.amber {
            background-color: #fdfaf2;
            color: #b45309;
        }
        .btn-container {
            text-align: center;
            margin-top: 28px;
            margin-bottom: 12px;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #4338ca;
        }
        .footer {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #71717a;
            border-top: 1px solid #f4f4f5;
        }
    </style>
</head>
<body>
    @php
        $isAdmitted = in_array($postulante->estado_admision, ['admitido_primera_opcion', 'admitido_segunda_opcion']);
        
        // Get carrera assigned name
        $carreraAsignada = '';
        if ($postulante->estado_admision === 'admitido_primera_opcion') {
            $carreraAsignada = $postulante->carreraPrimeraOpcion?->nombre ?? 'Primera Opción';
        } elseif ($postulante->estado_admision === 'admitido_segunda_opcion') {
            $carreraAsignada = $postulante->carreraSegundaOpcion?->nombre ?? 'Segunda Opción';
        }
    @endphp

    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header {{ $isAdmitted ? 'admitted' : 'other' }}">
                <h1>PROCESO DE ADMISIÓN CUP</h1>
                <p>Gestión Académica - Resultados Oficiales</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">Hola, {{ $postulante->nombres_apellidos }}</div>

                <div class="message">
                    @if($postulante->estado_admision === 'admitido_primera_opcion')
                        ¡Muchas felicidades! Nos complace informarte que has alcanzado un cupo y has sido **ADMITIDO** en tu carrera de primera opción para esta gestión académica.
                    @elseif($postulante->estado_admision === 'admitido_segunda_opcion')
                        ¡Muchas felicidades! Nos complace informarte que has alcanzado un cupo y has sido **ADMITIDO** en tu carrera de segunda opción para esta gestión académica.
                    @elseif($postulante->estado_admision === 'no_admitido')
                        Queremos informarte que has aprobado académicamente el proceso de evaluación. Sin embargo, debido al límite de plazas disponibles en esta convocatoria, los cupos fueron cubiertos por postulantes con calificaciones superiores y no lograste obtener una vacante.
                    @elseif($postulante->estado_admision === 'reprobado')
                        Te comunicamos que los resultados finales de tus evaluaciones del CUP no alcanzaron el puntaje mínimo aprobatorio requerido de 60.00 puntos por materia.
                    @else
                        Te informamos que tu estado en el sistema es: **{{ strtoupper($postulante->estado_admision) }}**.
                    @endif
                </div>

                <!-- Detalles -->
                <div class="card">
                    <div class="card-title">Resumen de Postulación</div>
                    
                    <div class="info-row">
                        <span class="info-label">Cédula de Identidad:</span>
                        <span class="info-value">{{ $postulante->ci }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Nota Final Promedio:</span>
                        <span class="info-value">{{ number_format($postulante->nota_final, 2) }} / 100</span>
                    </div>

                    @if($isAdmitted)
                        <div class="info-row">
                            <span class="info-label">Carrera Asignada:</span>
                            <span class="info-value" style="color: #4f46e5;">{{ $carreraAsignada }}</span>
                        </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label font-bold">Estado de Admisión:</span>
                        <span>
                            @if($postulante->estado_admision === 'admitido_primera_opcion')
                                <span class="badge emerald">Admitido (1ra Opción)</span>
                            @elseif($postulante->estado_admision === 'admitido_segunda_opcion')
                                <span class="badge teal">Admitido (2da Opción)</span>
                            @elseif($postulante->estado_admision === 'no_admitido')
                                <span class="badge amber">No Admitido (Sin Cupo)</span>
                            @elseif($postulante->estado_admision === 'reprobado')
                                <span class="badge rose">Reprobado</span>
                            @else
                                <span class="badge" style="background-color: #f4f4f5; color: #71717a;">{{ $postulante->estado_admision }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if($isAdmitted)
                    <div class="message" style="margin-top: 16px;">
                        Para consolidar tu ingreso y realizar tu inscripción formal, debes ingresar a tu portal de estudiante y efectuar el pago de tu matrícula de admisión académica.
                    </div>
                    <div class="btn-container">
                        <a href="{{ url('/dashboard') }}" class="btn">Acceder a mi Portal de Pagos</a>
                    </div>
                @elseif($postulante->estado_admision === 'no_admitido')
                    <div class="message" style="margin-top: 16px; font-size: 13px; color: #71717a;">
                        Tu nombre y puntaje permanecerán en nuestros registros por si se habilitan nuevas plazas o reasignaciones excepcionales de cupos no consolidados.
                    </div>
                @else
                    <div class="message" style="margin-top: 16px; font-size: 13px; color: #71717a;">
                        Te alentamos a seguir perseverando en tus metas profesionales y prepararte con entusiasmo para las próximas convocatorias académicas del CUP.
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                Este es un correo automático enviado por el Sistema de Admisiones CUP.<br>
                Por favor, no respondas a esta dirección de correo electrónico.<br>
                &copy; {{ date('Y') }} CUP - Universidad Autónoma. Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>
</html>
