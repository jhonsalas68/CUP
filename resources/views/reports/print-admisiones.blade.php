<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Oficial de Admisión - {{ $gestion->nombre }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            margin: 40px;
            background-color: #ffffff;
            line-height: 1.5;
        }
        .header {
            border-bottom: 3px double #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: border-between;
            align-items: center;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-title {
            font-size: 26px;
            font-weight: 800;
            color: #1e1b4b;
            letter-spacing: -0.5px;
        }
        .logo-subtitle {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #4f46e5;
            font-weight: 700;
            margin-top: 4px;
        }
        .report-info {
            text-align: right;
            font-size: 12px;
            color: #4b5563;
        }
        .title-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-title {
            font-size: 22px;
            font-weight: 850;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .report-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 6px;
            font-weight: 500;
        }
        .kpi-grid {
            display: grid;
            grid-template-cols: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        .kpi-val {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 2px;
        }
        .kpi-lbl {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-sub {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            font-size: 12px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .signatures {
            margin-top: 80px;
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 50px;
            text-align: center;
        }
        .sig-line {
            border-top: 1.5px solid #9ca3af;
            width: 70%;
            margin: 0 auto 8px auto;
        }
        .sig-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }
        .sig-subtitle {
            font-size: 10px;
            color: #6b7280;
        }
        .footer {
            margin-top: 60px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px dashed #e5e7eb;
            padding-top: 15px;
        }
        @media print {
            body { margin: 20px; font-size: 11px; }
            .no-print { display: none; }
            tr { page-break-inside: avoid; }
            .kpi-card { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div>
                <div class="logo-title">CUP - UAGRM</div>
                <div class="logo-subtitle">SISTEMA DE ADMISIÓN UNIVERSITARIA</div>
            </div>
        </div>
        <div class="report-info">
            <strong>Fecha de Reporte:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
            <strong>Operador:</strong> {{ auth()->user()->name }}
        </div>
    </div>

    <!-- Title -->
    <div class="title-container">
        <h2 class="report-title">Reporte de Admisión y Selección por Cupos</h2>
        <div class="report-subtitle">Gestión Académica / Semestre: <strong>{{ $gestion->nombre }}</strong></div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-val">{{ $stats['general']['total_postulantes'] }}</div>
            <div class="kpi-lbl">Total Postulantes</div>
            <div class="kpi-sub">Inscritos en la gestión</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-val" style="color: #10b981;">{{ $stats['general']['total_admitidos'] }}</div>
            <div class="kpi-lbl">Admitidos</div>
            <div class="kpi-sub">{{ $stats['general']['tasa_admision'] }}% de tasa de ingreso</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-val" style="color: #ef4444;">{{ $stats['general']['reprobados'] }}</div>
            <div class="kpi-lbl">Reprobados</div>
            <div class="kpi-sub">No aprobaron materias</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-val" style="color: #f59e0b;">{{ $stats['general']['no_admitidos'] }}</div>
            <div class="kpi-lbl">No Admitidos</div>
            <div class="kpi-sub">Pasaron pero sin cupo</div>
        </div>
    </div>

    <!-- Table -->
    <h3 style="font-size: 14px; font-weight: 700; border-bottom: 1.5px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 15px; color: #111827;">Resumen de Distribución de Plazas por Carrera</h3>
    <table>
        <thead>
            <tr>
                <th>Carrera</th>
                <th style="text-align: center;">Inscritos (1ra Opción)</th>
                <th style="text-align: center;">Cupos Habilitados (1ra/2da)</th>
                <th style="text-align: center;">Seleccionados (1ra/2da)</th>
                <th style="text-align: center;">No Admitidos</th>
                <th style="text-align: right;">Nota Mínima (Corte)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['carreras'] as $sigla => $cStats)
                <tr>
                    <td style="font-weight: 700;">
                        {{ $sigla }}
                        <span style="font-size: 10px; font-weight: normal; color: #6b7280; display: block; margin-top: 2px;">{{ $cStats['nombre'] }}</span>
                    </td>
                    <td style="text-align: center; font-weight: 500;">{{ $cStats['inscritos_primera_opcion'] }}</td>
                    <td style="text-align: center; color: #4b5563;">{{ $cStats['cupo_primera_opcion'] }} / {{ $cStats['cupo_segunda_opcion'] }}</td>
                    <td style="text-align: center;">
                        <strong style="color: #059669;">{{ $cStats['admitidos_primera_opcion'] }}</strong> /
                        <span style="color: #10b981; font-weight: 600;">{{ $cStats['admitidos_segunda_opcion'] }}</span>
                    </td>
                    <td style="text-align: center; color: #d97706; font-weight: 600;">{{ $cStats['no_admitidos'] }}</td>
                    <td style="text-align: right; font-weight: 800; color: #111827;">{{ number_format($cStats['nota_minima_ingreso'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Listado de Alumnos Admitidos por Carrera -->
    @if(count($admitidosPorCarrera) > 0)
        <div style="page-break-before: always;"></div>
        <div class="title-container" style="margin-top: 20px;">
            <h2 class="report-title" style="font-size: 18px;">Listado Oficial de Admitidos</h2>
            <div class="report-subtitle">Estudiantes seleccionados por orden de mérito y cupo</div>
        </div>
        
        @foreach($admitidosPorCarrera as $sigla => $data)
            <div style="margin-bottom: 30px; page-break-inside: avoid;">
                <h4 style="font-size: 13px; font-weight: 700; border-bottom: 2px solid #4f46e5; padding-bottom: 4px; margin-bottom: 10px; color: #1e1b4b; text-transform: uppercase;">
                    Carrera: {{ $data['carrera']->sigla }} - {{ $data['carrera']->nombre }} (Admitidos: {{ $data['alumnos']->count() }})
                </h4>
                <table style="margin-bottom: 10px;">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center; padding: 8px 6px;">Pos.</th>
                            <th style="width: 52%; padding: 8px 6px;">Nombre Completo</th>
                            <th style="width: 15%; padding: 8px 6px;">CI / Documento</th>
                            <th style="width: 15%; text-align: center; padding: 8px 6px;">Opción Ingreso</th>
                            <th style="width: 10%; text-align: right; padding: 8px 6px;">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $pos = 1; @endphp
                        @foreach($data['alumnos'] as $alumno)
                            <tr>
                                <td style="text-align: center; font-weight: 700; padding: 8px 6px;">#{{ $pos++ }}</td>
                                <td style="font-weight: 600; color: #111827; padding: 8px 6px;">{{ $alumno->nombres_apellidos }}</td>
                                <td style="padding: 8px 6px;">{{ $alumno->ci }}</td>
                                <td style="text-align: center; padding: 8px 6px;">
                                    @if($alumno->estado_admision === 'admitido_primera_opcion')
                                        <span style="color: #059669; font-weight: 700;">1ra Opción</span>
                                    @else
                                        <span style="color: #0d9488; font-weight: 700;">2da Opción</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #111827; padding: 8px 6px;">{{ number_format($alumno->nota_final, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <!-- Signatures -->
    <div class="signatures">
        <div>
            <div class="sig-line" style="margin-top: 40px;"></div>
            <div class="sig-title">Coordinador Académico CUP</div>
            <div class="sig-subtitle">Universidad Autónoma Gabriel René Moreno</div>
        </div>
        <div>
            <div class="sig-line" style="margin-top: 40px;"></div>
            <div class="sig-title">Administrador del Sistema</div>
            <div class="sig-subtitle">Departamento de Admisión y Registro</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Este reporte es un documento oficial emitido por el Sistema de Admisión de la UAGRM.
        Toda falsificación o alteración de los datos aquí contenidos anula la validez del proceso de admisión.
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
