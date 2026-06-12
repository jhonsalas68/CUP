<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ ucfirst($tabla) }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            margin: 30px;
            background-color: #ffffff;
        }
        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e1b4b;
            letter-spacing: -0.5px;
        }
        .logo-subtitle {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6b7280;
            font-weight: 600;
        }
        .report-info {
            text-align: right;
            font-size: 12px;
            color: #4b5563;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .meta-grid {
            display: grid;
            grid-template-cols: repeat(3, 1fr);
            gap: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 13px;
        }
        .meta-item strong {
            color: #374151;
            display: block;
            margin-bottom: 4px;
        }
        .meta-item span {
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 12px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }
        tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-neutral { background-color: #f3f4f6; color: #374151; }
        .footer {
            margin-top: 50px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px dashed #e5e7eb;
            padding-top: 15px;
        }
        @media print {
            body { margin: 15px; font-size: 11px; }
            .no-print { display: none; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div>
                <div class="logo-title">CUP - ADMISIÓN</div>
                <div class="logo-subtitle">Gestión Académica</div>
            </div>
        </div>
        <div class="report-info">
            <strong>Generado el:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
            <strong>Usuario:</strong> {{ auth()->user()->name }}
        </div>
    </div>

    <!-- Title -->
    <div class="title">Reporte de {{ ucfirst($tabla) }}</div>

    <!-- Meta Grid -->
    <div class="meta-grid">
        <div class="meta-item">
            <strong>Filtro Tabla</strong>
            <span>{{ ucfirst($tabla) }}</span>
        </div>
        <div class="meta-item">
            <strong>Semestre / Gestión</strong>
            <span>{{ $gestion ? $gestion->nombre : 'Todos' }}</span>
        </div>
        <div class="meta-item">
            <strong>Carrera Seleccionada</strong>
            <span>{{ $carrera ? $carrera->nombre : 'Todas' }}</span>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                @foreach($columnas as $col)
                    <th>{{ $headersMap[$col] ?? ucfirst($col) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    @foreach($columnas as $col)
                        <td>
                            @if($col === 'nota_final')
                                <strong style="color: {{ $item->nota_final >= 60.00 ? '#059669' : '#dc2626' }}">
                                    {{ $item->nota_final !== null ? number_format($item->nota_final, 2) : '—' }}
                                </strong>
                            @elseif($col === 'estado_admision')
                                @php
                                    $status = $item->estado_admision;
                                    $class = 'badge-neutral';
                                    if (str_starts_with($status, 'admitido')) $class = 'badge-success';
                                    elseif ($status === 'reprobado') $class = 'badge-danger';
                                    elseif ($status === 'pendiente') $class = 'badge-warning';
                                @endphp
                                <span class="badge {{ $class }}">{{ str_replace('_', ' ', $status) }}</span>
                            @elseif($col === 'nombre' && $tabla === 'docentes')
                                <strong>{{ $item->nombre ?? $item->user?->name ?? '—' }}</strong>
                            @elseif($col === 'nombre' && $tabla === 'postulantes')
                                <strong>{{ $item->nombres_apellidos ?? $item->user?->name ?? '—' }}</strong>
                            @else
                                @if($col === 'id')
                                    {{ $item->id }}
                                @elseif($col === 'sigla')
                                    {{ $item->sigla ?? '—' }}
                                @elseif($col === 'nombre')
                                    {{ $item->nombre ?? '—' }}
                                @elseif($col === 'materias_count')
                                    {{ $item->materias_count ?? 0 }}
                                @elseif($col === 'email')
                                    {{ $item->user?->email ?? '—' }}
                                @elseif($col === 'ci')
                                    {{ $item->ci ?? '—' }}
                                @elseif($col === 'telefono')
                                    {{ $item->telefono ?? '—' }}
                                @elseif($col === 'especialidad')
                                    {{ $item->especialidad ?? '—' }}
                                @elseif($col === 'formacion_academica')
                                    {{ $item->formacion_academica ?? '—' }}
                                @elseif($col === 'materia')
                                    {{ $item->materia?->nombre ?? '—' }}
                                @elseif($col === 'carrera')
                                    {{ $item->materia?->carrera?->nombre ?? ($item->carrera?->nombre ?? '—') }}
                                @elseif($col === 'gestion')
                                    {{ $item->gestion?->nombre ?? '—' }}
                                @elseif($col === 'ponderacion')
                                    {{ $item->ponderacion }}%
                                @elseif($col === 'fecha')
                                    {{ $item->fecha ? $item->fecha->format('d/m/Y') : '—' }}
                                @elseif($col === 'sexo')
                                    {{ $item->sexo ?? '—' }}
                                @elseif($col === 'colegio_procedencia')
                                    {{ $item->colegio_procedencia ?? '—' }}
                                @elseif($col === 'ciudad')
                                    {{ $item->ciudad ?? '—' }}
                                @elseif($col === 'carrera_primera_opcion')
                                    {{ $item->carreraPrimeraOpn?->nombre ?? '—' }}
                                @elseif($col === 'carrera_segunda_opcion')
                                    {{ $item->carreraSegundaOpn?->nombre ?? '—' }}
                                @elseif($col === 'docente')
                                    @php
                                        $gIdForDoc = $tabla === 'examenes' ? $item->gestion_id : ($gestion ? $gestion->id : (\App\Models\Gestion::where('activo', true)->first()?->id));
                                        $mIdForDoc = $tabla === 'examenes' ? $item->materia_id : $item->id;
                                        $groupIdsForDoc = \App\Models\Grupo::where('materia_id', $mIdForDoc)
                                            ->where('gestion_id', $gIdForDoc)
                                            ->pluck('id');
                                        $docNames = \App\Models\Docente::whereHas('grupos', function($q) use ($groupIdsForDoc) {
                                            $q->whereIn('grupos.id', $groupIdsForDoc);
                                        })->pluck('nombre')->unique();
                                        $docTxt = $docNames->isNotEmpty() ? $docNames->implode(', ') : 'No asignado';
                                    @endphp
                                    {{ $docTxt }}
                                @elseif($col === 'alumnos')
                                    @php
                                        $gIdForAl = $tabla === 'examenes' ? $item->gestion_id : ($gestion ? $gestion->id : (\App\Models\Gestion::where('activo', true)->first()?->id));
                                        $mIdForAl = $tabla === 'examenes' ? $item->materia_id : $item->id;
                                        $groupIdsForAl = \App\Models\Grupo::where('materia_id', $mIdForAl)
                                            ->where('gestion_id', $gIdForAl)
                                            ->pluck('id');
                                        $alNames = \App\Models\Postulante::whereHas('grupos', function($q) use ($groupIdsForAl) {
                                            $q->whereIn('grupos.id', $groupIdsForAl);
                                        })->pluck('nombres_apellidos')->unique();
                                        $alTxt = $alNames->isNotEmpty() ? $alNames->implode(', ') : 'Ninguno';
                                    @endphp
                                    {{ $alTxt }}
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columnas) }}" style="text-align: center; color: #9ca3af; padding: 20px;">
                        No se encontraron registros con los criterios seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte oficial del CUP - UAGRM. Generado automáticamente por el Sistema de Admisión.
    </div>

    <!-- Automatically open the print dialog when loading -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
