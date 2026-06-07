<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Docente;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Cupo;
use App\Models\Postulante;
use App\Models\Examen;
use App\Models\Nota;
use App\Services\GroupGenerationService;
use App\Services\ExamService;
use App\Services\AdmissionSelectionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardMockSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza de tablas transaccionales y maestras
        DB::statement('TRUNCATE TABLE notas CASCADE;');
        DB::statement('TRUNCATE TABLE examenes CASCADE;');
        DB::statement('TRUNCATE TABLE horarios CASCADE;');
        DB::statement('TRUNCATE TABLE postulante_grupo CASCADE;');
        DB::statement('TRUNCATE TABLE asignaciones_docente CASCADE;');
        DB::statement('TRUNCATE TABLE grupos CASCADE;');
        DB::statement('TRUNCATE TABLE docente_materia CASCADE;');
        
        // Eliminar usuarios que no sean admin o coordinador
        User::role(['Docente', 'Postulante'])->forceDelete();
        
        DB::statement('TRUNCATE TABLE postulantes CASCADE;');
        DB::statement('TRUNCATE TABLE docentes CASCADE;');
        DB::statement('TRUNCATE TABLE cupos CASCADE;');
        DB::statement('TRUNCATE TABLE materias CASCADE;');
        DB::statement('TRUNCATE TABLE carreras CASCADE;');
        DB::statement('TRUNCATE TABLE gestiones CASCADE;');

        // Asegurar que existan los roles
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Crear Gestiones (2 históricas y 1 activa)
        $g1 = Gestion::create([
            'nombre' => 'I-2025',
            'fecha_inicio' => '2025-02-01',
            'fecha_fin' => '2025-06-30',
            'activo' => false,
        ]);

        $g2 = Gestion::create([
            'nombre' => 'II-2025',
            'fecha_inicio' => '2025-08-01',
            'fecha_fin' => '2025-12-31',
            'activo' => false,
        ]);

        $gActive = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        // 3. Crear Carreras
        $sis = Carrera::create(['nombre' => 'Ingeniería de Sistemas', 'sigla' => 'SIS']);
        $inf = Carrera::create(['nombre' => 'Ingeniería Informática', 'sigla' => 'INF']);
        $red = Carrera::create(['nombre' => 'Ingeniería en Redes y Telecomunicaciones', 'sigla' => 'RED']);
        $rob = Carrera::create(['nombre' => 'Ingeniería Robótica', 'sigla' => 'ROB']);

        $carreras = [$sis, $inf, $red, $rob];

        // 4. Configurar Cupos por gestión
        $cuposData = [
            'I-2025' => [
                'SIS' => [30, 15], 'INF' => [20, 10], 'RED' => [15, 5], 'ROB' => [12, 6]
            ],
            'II-2025' => [
                'SIS' => [35, 15], 'INF' => [25, 10], 'RED' => [18, 5], 'ROB' => [15, 7]
            ],
            'I-2026' => [
                'SIS' => [40, 20], 'INF' => [30, 15], 'RED' => [20, 8], 'ROB' => [18, 8]
            ]
        ];

        foreach ([$g1, $g2, $gActive] as $g) {
            foreach ($carreras as $c) {
                $caps = $cuposData[$g->nombre][$c->sigla];
                Cupo::create([
                    'carrera_id' => $c->id,
                    'gestion_id' => $g->id,
                    'cantidad_primera_opcion' => $caps[0],
                    'cantidad_segunda_opcion' => $caps[1],
                ]);
            }
        }

        // 5. Crear Materias por carrera
        $mSistemas = [
            ['nombre' => 'Matemáticas (Sistemas)', 'sigla' => 'MAT-SIS'],
            ['nombre' => 'Física (Sistemas)', 'sigla' => 'FIS-SIS'],
            ['nombre' => 'Inglés (Sistemas)', 'sigla' => 'ING-SIS'],
            ['nombre' => 'Computación (Sistemas)', 'sigla' => 'COM-SIS'],
        ];

        $mInformatica = [
            ['nombre' => 'Matemáticas (Informática)', 'sigla' => 'MAT-INF'],
            ['nombre' => 'Física (Informática)', 'sigla' => 'FIS-INF'],
            ['nombre' => 'Inglés (Informática)', 'sigla' => 'ING-INF'],
            ['nombre' => 'Computación (Informática)', 'sigla' => 'COM-INF'],
        ];

        $mRedes = [
            ['nombre' => 'Matemáticas (Redes)', 'sigla' => 'MAT-RED'],
            ['nombre' => 'Física (Redes)', 'sigla' => 'FIS-RED'],
            ['nombre' => 'Inglés (Redes)', 'sigla' => 'ING-RED'],
            ['nombre' => 'Computación (Redes)', 'sigla' => 'COM-RED'],
        ];

        $mRobotica = [
            ['nombre' => 'Matemáticas (Robótica)', 'sigla' => 'MAT-ROB'],
            ['nombre' => 'Física (Robótica)', 'sigla' => 'FIS-ROB'],
            ['nombre' => 'Inglés (Robótica)', 'sigla' => 'ING-ROB'],
            ['nombre' => 'Computación (Robótica)', 'sigla' => 'COM-ROB'],
        ];

        $materiasList = [];

        foreach ($mSistemas as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $sis->id]));
        }
        foreach ($mInformatica as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $inf->id]));
        }
        foreach ($mRedes as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $red->id]));
        }
        foreach ($mRobotica as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $rob->id]));
        }

        // 6. Crear Docentes y asociar disponibilidad
        $docentesData = [
            ['name' => 'Carlos Mendoza', 'email' => 'carlos@cup.edu.bo', 'esp' => 'Computación y Algoritmos', 'materias' => ['COM-SIS', 'COM-INF', 'COM-RED', 'COM-ROB']],
            ['name' => 'María Delgadillo', 'email' => 'maria@cup.edu.bo', 'esp' => 'Matemáticas y Álgebra', 'materias' => ['MAT-SIS', 'MAT-INF', 'MAT-RED', 'MAT-ROB']],
            ['name' => 'Jorge Vaca', 'email' => 'jorge@cup.edu.bo', 'esp' => 'Física General', 'materias' => ['FIS-SIS', 'FIS-INF', 'FIS-RED', 'FIS-ROB']],
            ['name' => 'Dra. Elena Prado', 'email' => 'elena@cup.edu.bo', 'esp' => 'Inglés Técnico', 'materias' => ['ING-SIS', 'ING-INF', 'ING-RED', 'ING-ROB']],
            ['name' => 'Dr. René Justiniano', 'email' => 'rene@cup.edu.bo', 'esp' => 'Computación e Inglés', 'materias' => ['COM-SIS', 'COM-INF', 'ING-SIS', 'ING-INF', 'COM-ROB', 'ING-ROB']],
        ];

        $docentesList = [];
        foreach ($docentesData as $dIdx => $d) {
            $u = User::create([
                'name' => $d['name'],
                'email' => $d['email'],
                'password' => Hash::make('password'),
            ]);
            $u->assignRole('Docente');

            $doc = Docente::create([
                'user_id' => $u->id,
                'ci' => '777888' . $dIdx,
                'telefono' => '7891234' . $dIdx,
                'especialidad' => $d['esp'],
                'disponibilidad_horaria' => ['slot_1', 'slot_2', 'slot_3', 'slot_4', 'slot_5', 'slot_6', 'slot_7', 'slot_8'],
                'formacion_academica' => 'Maestría en Educación Superior y Licenciatura en la Especialidad.',
            ]);

            $mIds = Materia::whereIn('sigla', $d['materias'])->pluck('id')->toArray();
            $doc->materias()->attach($mIds);
            $docentesList[] = $doc;
        }

        // 7. Instanciar Servicios para el llenado masivo y consistente
        $groupService = new GroupGenerationService();
        $examService = new ExamService();
        $admissionService = new AdmissionSelectionService();

        // 8. Poblar Gestiones Históricas (I-2025 y II-2025)
        $this->seedHistoricalGestion($g1, 100, $carreras, $groupService, $examService, $admissionService);
        $this->seedHistoricalGestion($g2, 120, $carreras, $groupService, $examService, $admissionService);

        // 9. Poblar Gestión Activa (I-2026) con postulantes y notas
        $this->seedHistoricalGestion($gActive, 180, $carreras, $groupService, $examService, $admissionService);
    }

    private function seedHistoricalGestion(
        Gestion $g,
        int $numPostulantes,
        array $carreras,
        GroupGenerationService $groupService,
        ExamService $examService,
        AdmissionSelectionService $admissionService
    ): void {
        $carrSIS = $carreras[0];
        $carrINF = $carreras[1];
        $carrRED = $carreras[2];
        $carrROB = $carreras[3];

        // Crear postulantes con distribución: SIS 40%, RED 20%, INF 20%, ROB 20%
        for ($i = 1; $i <= $numPostulantes; $i++) {
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $primera = $carrSIS;
                $segunda = rand(1, 2) == 1 ? $carrINF : null;
            } elseif ($rand <= 60) {
                $primera = $carrRED;
                $segunda = null;
            } elseif ($rand <= 80) {
                $primera = $carrINF;
                $segunda = rand(1, 2) == 1 ? $carrSIS : null;
            } else {
                $primera = $carrROB;
                $segunda = rand(1, 2) == 1 ? $carrINF : null;
            }

            $u = User::create([
                'name' => "Postulante {$g->nombre} #{$i}",
                'email' => "postulante_{$g->nombre}_{$i}@cup.edu.bo",
                'password' => Hash::make('password'),
            ]);
            $u->assignRole('Postulante');

            Postulante::create([
                'user_id' => $u->id,
                'ci' => rand(1000000, 9999999),
                'telefono' => rand(60000000, 79999999),
                'fecha_nacimiento' => today()->subYears(rand(17, 22))->format('Y-m-d'),
                'carrera_primera_opcion_id' => $primera->id,
                'carrera_segunda_opcion_id' => $segunda ? $segunda->id : null,
                'gestion_id' => $g->id,
                'estado_admision' => 'pendiente',
                'ci_vigente' => true,
                'titulo_bachiller' => true,
                'libreta_legalizada' => true,
            ]);
        }

        // Generar grupos
        $groupService->generate($g->id);

        // Crear exámenes para todas las materias de esta gestión
        $materias = Materia::all();
        $examMap = []; // $examMap[$materiaId][type] = examModel
        foreach ($materias as $m) {
            $examMap[$m->id]['Primer Parcial'] = $examService->createExam($m->id, $g->id, 'Primer Parcial', today()->addDays(10)->format('Y-m-d'));
            $examMap[$m->id]['Segundo Parcial'] = $examService->createExam($m->id, $g->id, 'Segundo Parcial', today()->addDays(20)->format('Y-m-d'));
            $examMap[$m->id]['Examen Final'] = $examService->createExam($m->id, $g->id, 'Examen Final', today()->addDays(30)->format('Y-m-d'));
        }

        // Calificar a los postulantes
        // Para simular rendimiento real, algunos aprueban y otros reprueban
        $postulantes = Postulante::where('gestion_id', $g->id)->get();
        foreach ($postulantes as $p) {
            $carreraId = $p->carrera_primera_opcion_id;
            $mats = Materia::where('carrera_id', $carreraId)->get();

            foreach ($mats as $m) {
                // Generar notas coherentes: aprobados sacan entre 60 y 100, reprobados entre 20 y 59
                // Un 75% de probabilidad de aprobar
                $esAprobado = rand(1, 100) <= 75;
                if ($esAprobado) {
                    $n1 = rand(60, 100);
                    $n2 = rand(60, 100);
                    $n3 = rand(60, 100);
                } else {
                    $n1 = rand(30, 70);
                    $n2 = rand(30, 70);
                    $n3 = rand(20, 58); // Nota baja
                }

                $exam1 = $examMap[$m->id]['Primer Parcial'];
                $exam2 = $examMap[$m->id]['Segundo Parcial'];
                $exam3 = $examMap[$m->id]['Examen Final'];

                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam1->id, 'puntaje' => $n1]);
                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam2->id, 'puntaje' => $n2]);
                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam3->id, 'puntaje' => $n3]);
            }
        }

        // Procesar admisiones y rankings para esta gestión
        $admissionService->processAdmissions($g->id);
    }
}
