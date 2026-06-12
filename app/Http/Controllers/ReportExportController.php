<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\Gestion;
use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Materia;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function exportPostulantes(Request $request)
    {
        $gestionId = $request->query('gestion_id');
        $gestion = Gestion::find($gestionId);
        $gestionName = $gestion ? str_replace(' ', '_', $gestion->nombre) : 'todos';

        $response = new StreamedResponse(function () use ($gestionId) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, [
                'ID',
                'Nombre completo',
                'Correo electrónico',
                'CI',
                'Teléfono',
                'Sexo',
                'Colegio de Procedencia',
                'Ciudad',
                'Carrera 1ra Opción',
                'Carrera 2da Opción',
                'Semestre / Gestión',
                'Nota Final',
                'Estado de Admisión'
            ], ';');

            $query = Postulante::with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion']);
            if ($gestionId) {
                $query->where('gestion_id', $gestionId);
            }

            $query->chunk(200, function ($postulantes) use ($handle) {
                foreach ($postulantes as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->nombres_apellidos ?? $p->user?->name ?? '—',
                        $p->user?->email ?? '—',
                        $p->ci,
                        $p->telefono ?? '—',
                        $p->sexo,
                        $p->colegio_procedencia ?? '—',
                        $p->ciudad ?? '—',
                        $p->carreraPrimeraOpn?->nombre ?? '—',
                        $p->carreraSegundaOpn?->nombre ?? '—',
                        $p->gestion?->nombre ?? '—',
                        $p->nota_final !== null ? number_format($p->nota_final, 2) : '—',
                        ucfirst(str_replace('_', ' ', $p->estado_admision))
                    ], ';');
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="postulantes_' . $gestionName . '.csv"');

        return $response;
    }

    public function exportAdmitidos(Request $request)
    {
        $gestionId = $request->query('gestion_id');
        $gestion = Gestion::find($gestionId);
        $gestionName = $gestion ? str_replace(' ', '_', $gestion->nombre) : 'todos';

        $response = new StreamedResponse(function () use ($gestionId) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, [
                'ID',
                'Nombre completo',
                'Correo electrónico',
                'CI',
                'Teléfono',
                'Carrera Asignada',
                'Semestre / Gestión',
                'Nota Final',
                'Estado de Admisión'
            ], ';');

            $query = Postulante::with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion'])
                ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion']);
                
            if ($gestionId) {
                $query->where('gestion_id', $gestionId);
            }

            $query->chunk(200, function ($postulantes) use ($handle) {
                foreach ($postulantes as $p) {
                    $carreraAsignada = '—';
                    if ($p->estado_admision === 'admitido_primera_opcion') {
                        $carreraAsignada = $p->carreraPrimeraOpn?->nombre ?? '—';
                    } elseif ($p->estado_admision === 'admitido_segunda_opcion') {
                        $carreraAsignada = $p->carreraSegundaOpn?->nombre ?? '—';
                    }

                    fputcsv($handle, [
                        $p->id,
                        $p->nombres_apellidos ?? $p->user?->name ?? '—',
                        $p->user?->email ?? '—',
                        $p->ci,
                        $p->telefono ?? '—',
                        $carreraAsignada,
                        $p->gestion?->nombre ?? '—',
                        $p->nota_final !== null ? number_format($p->nota_final, 2) : '—',
                        $p->estado_admision === 'admitido_primera_opcion' ? 'Admitido (1ra opción)' : 'Admitido (2da opción)'
                    ], ';');
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="admitidos_' . $gestionName . '.csv"');

        return $response;
    }

    public function exportNoAdmitidos(Request $request)
    {
        $gestionId = $request->query('gestion_id');
        $gestion = Gestion::find($gestionId);
        $gestionName = $gestion ? str_replace(' ', '_', $gestion->nombre) : 'todos';

        $response = new StreamedResponse(function () use ($gestionId) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, [
                'ID',
                'Nombre completo',
                'Correo electrónico',
                'CI',
                'Teléfono',
                'Carrera Postulada (1ra Opción)',
                'Carrera Postulada (2da Opción)',
                'Semestre / Gestión',
                'Nota Final',
                'Estado de Admisión'
            ], ';');

            $query = Postulante::with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion'])
                ->whereIn('estado_admision', ['no_admitido', 'reprobado']);
                
            if ($gestionId) {
                $query->where('gestion_id', $gestionId);
            }

            $query->chunk(200, function ($postulantes) use ($handle) {
                foreach ($postulantes as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->nombres_apellidos ?? $p->user?->name ?? '—',
                        $p->user?->email ?? '—',
                        $p->ci,
                        $p->telefono ?? '—',
                        $p->carreraPrimeraOpn?->nombre ?? '—',
                        $p->carreraSegundaOpn?->nombre ?? '—',
                        $p->gestion?->nombre ?? '—',
                        $p->nota_final !== null ? number_format($p->nota_final, 2) : '—',
                        ucfirst(str_replace('_', ' ', $p->estado_admision))
                    ], ';');
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="no_admitidos_y_reprobados_' . $gestionName . '.csv"');

        return $response;
    }

    public function imprimirReporteAdmision(Request $request, \App\Services\AdmissionSelectionService $service)
    {
        $gestionId = $request->query('gestion_id');
        $gestion = Gestion::find($gestionId);
        
        if (!$gestion) {
            abort(404, 'Gestión no encontrada.');
        }

        $stats = $service->getStats($gestionId);

        // Fetch admitted candidates grouped by career
        $admitidos = Postulante::where('gestion_id', $gestionId)
            ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion'])
            ->orderByDesc('nota_final')
            ->orderBy('id')
            ->get();

        $carreras = Carrera::all();
        $admitidosPorCarrera = [];
        
        foreach ($carreras as $carrera) {
            $carreraAdmitidos = $admitidos->filter(function ($p) use ($carrera) {
                return ($p->estado_admision === 'admitido_primera_opcion' && $p->carrera_primera_opcion_id === $carrera->id)
                    || ($p->estado_admision === 'admitido_segunda_opcion' && $p->carrera_segunda_opcion_id === $carrera->id);
            });
            
            if ($carreraAdmitidos->isNotEmpty()) {
                $admitidosPorCarrera[$carrera->sigla] = [
                    'carrera' => $carrera,
                    'alumnos' => $carreraAdmitidos
                ];
            }
        }

        return view('reports.print-admisiones', [
            'gestion' => $gestion,
            'stats' => $stats,
            'admitidosPorCarrera' => $admitidosPorCarrera
        ]);
    }

    public function exportarPersonalizado(Request $request)
    {
        $tabla = $request->query('tabla', 'postulantes');
        $gestionId = $request->query('gestion_id');
        $carreraId = $request->query('carrera_id');
        $formato = $request->query('formato', 'excel');

        // Parse selected columns
        $columnasInput = $request->query('columnas');
        $columnas = $columnasInput ? explode(',', $columnasInput) : [];

        $columnasDefecto = [
            'carreras' => ['id', 'sigla', 'nombre', 'materias_count'],
            'docentes' => ['id', 'nombre', 'email', 'ci', 'telefono', 'especialidad', 'profesional_area', 'tiene_maestria', 'tiene_diplomado'],
            'examenes' => ['id', 'nombre', 'materia', 'carrera', 'gestion', 'docente', 'alumnos', 'ponderacion', 'fecha'],
            'postulantes' => ['id', 'nombre', 'email', 'ci', 'telefono', 'carrera_primera_opcion', 'carrera_segunda_opcion', 'gestion', 'nota_final', 'estado_admision'],
            'materias' => ['id', 'sigla', 'nombre', 'carrera', 'docente', 'alumnos']
        ];

        if (empty($columnas) || count($columnas) === 1 && empty($columnas[0])) {
            $columnas = $columnasDefecto[$tabla] ?? [];
        }

        $gestion = Gestion::find($gestionId);
        $carrera = Carrera::find($carreraId);

        $data = $this->getData($tabla, $gestionId, $carreraId);

        $headersMap = [
            'id' => 'ID',
            'sigla' => 'Sigla',
            'nombre' => 'Nombre',
            'materias_count' => 'Materias Habilitadas',
            
            'email' => 'Correo electrónico',
            'ci' => 'CI',
            'telefono' => 'Teléfono',
            'especialidad' => 'Especialidad',
            'formacion_academica' => 'Formación Académica',
            'profesional_area' => 'Profesional en Área',
            'tiene_maestria' => 'Tiene Maestría',
            'tiene_diplomado' => 'Tiene Diplomado',
            
            'materia' => 'Materia',
            'carrera' => 'Carrera',
            'gestion' => 'Gestión',
            'docente' => 'Docente(s)',
            'alumnos' => 'Alumnos / Postulantes',
            'ponderacion' => 'Ponderación',
            'fecha' => 'Fecha',
            
            'sexo' => 'Sexo',
            'colegio_procedencia' => 'Colegio de Procedencia',
            'ciudad' => 'Ciudad',
            'carrera_primera_opcion' => 'Carrera 1ra Opción',
            'carrera_segunda_opcion' => 'Carrera 2da Opción',
            'nota_final' => 'Nota Final',
            'estado_admision' => 'Estado de Admisión'
        ];

        if ($formato === 'excel') {
            return $this->exportCsv($tabla, $data, $gestion, $carrera, $columnas, $headersMap);
        } else {
            return $this->exportPdf($tabla, $data, $gestion, $carrera, $columnas, $headersMap);
        }
    }

    private function getData($tabla, $gestionId, $carreraId)
    {
        switch ($tabla) {
            case 'carreras':
                $query = Carrera::query()->withCount('materias');
                if ($carreraId) {
                    $query->where('id', $carreraId);
                }
                return $query->orderBy('nombre')->get();

            case 'docentes':
                $query = Docente::query()->with('user');
                if ($carreraId) {
                    $query->whereHas('grupos.materia', function ($q) use ($carreraId) {
                        $q->where('carrera_id', $carreraId);
                    });
                }
                if ($gestionId) {
                    $query->whereHas('grupos', function ($q) use ($gestionId) {
                        $q->where('gestion_id', $gestionId);
                    });
                }
                return $query->orderBy('id', 'desc')->get();

            case 'examenes':
                $query = Examen::query()->with(['materia.carrera', 'gestion']);
                if ($gestionId) {
                    $query->where('gestion_id', $gestionId);
                }
                if ($carreraId) {
                    $query->whereHas('materia', function ($q) use ($carreraId) {
                        $q->where('carrera_id', $carreraId);
                    });
                }
                return $query->orderBy('fecha', 'desc')->get();

            case 'materias':
                $query = Materia::query()->with('carrera');
                if ($carreraId) {
                    $query->where('carrera_id', $carreraId);
                }
                return $query->orderBy('nombre')->get();

            case 'postulantes':
            default:
                $query = Postulante::query()->with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion']);
                if ($gestionId) {
                    $query->where('gestion_id', $gestionId);
                }
                if ($carreraId) {
                    $query->where(function ($q) use ($carreraId) {
                        $q->where('carrera_primera_opcion_id', $carreraId)
                          ->orWhere('carrera_segunda_opcion_id', $carreraId);
                    });
                }
                return $query->orderBy('id', 'desc')->get();
        }
    }

    private function exportCsv($tabla, $data, $gestion, $carrera, $columnas, $headersMap)
    {
        $gestionName = $gestion ? str_replace(' ', '_', $gestion->nombre) : 'todos';
        $filename = "reporte_{$tabla}_{$gestionName}.csv";

        $response = new StreamedResponse(function () use ($tabla, $data, $columnas, $headersMap) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // Header
            $headers = [];
            foreach ($columnas as $col) {
                $headers[] = $headersMap[$col] ?? ucfirst($col);
            }
            fputcsv($handle, $headers, ';');

            // Data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($columnas as $col) {
                    $row[] = $this->getColumnValue($tabla, $item, $col);
                }
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    private function getColumnValue($tabla, $item, $col)
    {
        switch ($col) {
            case 'id':
                return $item->id;
            case 'sigla':
                return $item->sigla ?? '—';
            case 'nombre':
                 if ($tabla === 'docentes') {
                     return $item->nombre ?? $item->user?->name ?? '—';
                 }
                 if ($tabla === 'postulantes') {
                     return $item->nombres_apellidos ?? $item->user?->name ?? '—';
                 }
                return $item->nombre ?? '—';
            case 'materias_count':
                return $item->materias_count ?? 0;
            case 'email':
                return $item->user?->email ?? '—';
            case 'ci':
                return $item->ci ?? '—';
            case 'telefono':
                return $item->telefono ?? '—';
            case 'especialidad':
                return $item->especialidad ?? '—';
            case 'formacion_academica':
                return $item->formacion_academica ?? '—';
            case 'profesional_area':
                return $item->profesional_area ? 'Sí' : 'No';
            case 'tiene_maestria':
                return $item->tiene_maestria ? 'Sí' : 'No';
            case 'tiene_diplomado':
                return $item->tiene_diplomado ? 'Sí' : 'No';
            case 'materia':
                return $item->materia?->nombre ?? '—';
            case 'carrera':
                if ($tabla === 'materias') {
                    return $item->carrera?->nombre ?? '—';
                }
                return $item->materia?->carrera?->nombre ?? '—';
            case 'gestion':
                if ($tabla === 'examenes') {
                    return $item->gestion?->nombre ?? '—';
                }
                return $item->gestion?->nombre ?? '—';
            case 'ponderacion':
                return ($item->ponderacion ?? 0) . '%';
            case 'fecha':
                return $item->fecha ? $item->fecha->format('d/m/Y') : '—';
            case 'docente':
                if ($tabla === 'examenes') {
                    return $item->docentes_names;
                }
                if ($tabla === 'materias') {
                    $gId = request()->query('gestion_id');
                    if (!$gId) {
                        $activeG = \App\Models\Gestion::where('activo', true)->first();
                        $gId = $activeG ? $activeG->id : null;
                    }
                    $groupIds = \App\Models\Grupo::where('materia_id', $item->id)
                        ->when($gId, fn($q) => $q->where('gestion_id', $gId))
                        ->pluck('id');
                    if ($groupIds->isEmpty()) return 'No asignado';
                    $docenteNames = \App\Models\Docente::whereHas('grupos', function($q) use ($groupIds) {
                        $q->whereIn('grupos.id', $groupIds);
                    })->pluck('nombre')->unique();
                    return $docenteNames->isNotEmpty() ? $docenteNames->implode(', ') : 'No asignado';
                }
                return '—';
            case 'alumnos':
                if ($tabla === 'examenes') {
                    return $item->alumnos_names;
                }
                if ($tabla === 'materias') {
                    $gId = request()->query('gestion_id');
                    if (!$gId) {
                        $activeG = \App\Models\Gestion::where('activo', true)->first();
                        $gId = $activeG ? $activeG->id : null;
                    }
                    $groupIds = \App\Models\Grupo::where('materia_id', $item->id)
                        ->when($gId, fn($q) => $q->where('gestion_id', $gId))
                        ->pluck('id');
                    if ($groupIds->isEmpty()) return 'Ninguno';
                    $alumnoNames = \App\Models\Postulante::whereHas('grupos', function($q) use ($groupIds) {
                        $q->whereIn('grupos.id', $groupIds);
                    })->pluck('nombres_apellidos')->unique();
                    return $alumnoNames->isNotEmpty() ? $alumnoNames->implode(', ') : 'Ninguno';
                }
                return '—';
            case 'sexo':
                return $item->sexo ?? '—';
            case 'colegio_procedencia':
                return $item->colegio_procedencia ?? '—';
            case 'ciudad':
                return $item->ciudad ?? '—';
            case 'carrera_primera_opcion':
                return $item->carreraPrimeraOpn?->nombre ?? '—';
            case 'carrera_segunda_opcion':
                return $item->carreraSegundaOpn?->nombre ?? '—';
            case 'nota_final':
                return $item->nota_final !== null ? number_format($item->nota_final, 2) : '—';
            case 'estado_admision':
                return ucfirst(str_replace('_', ' ', $item->estado_admision ?? '—'));
            default:
                return '—';
        }
    }

    private function exportPdf($tabla, $data, $gestion, $carrera, $columnas, $headersMap)
    {
        return view('reports.print', [
            'tabla' => $tabla,
            'data' => $data,
            'gestion' => $gestion,
            'carrera' => $carrera,
            'columnas' => $columnas,
            'headersMap' => $headersMap
        ]);
    }
}
