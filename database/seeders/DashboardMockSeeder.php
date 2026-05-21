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
        $civ = Carrera::create(['nombre' => 'Ingeniería Civil', 'sigla' => 'CIV']);
        $med = Carrera::create(['nombre' => 'Medicina', 'sigla' => 'MED']);

        $carreras = [$sis, $civ, $med];

        // 4. Configurar Cupos por gestión
        $cuposData = [
            'I-2025' => [
                'SIS' => [30, 15], 'CIV' => [20, 10], 'MED' => [15, 5]
            ],
            'II-2025' => [
                'SIS' => [35, 15], 'CIV' => [25, 10], 'MED' => [18, 5]
            ],
            'I-2026' => [
                'SIS' => [40, 20], 'CIV' => [30, 15], 'MED' => [20, 8]
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
            ['nombre' => 'Introducción a la Programación', 'sigla' => 'SIS-110'],
            ['nombre' => 'Álgebra Lineal', 'sigla' => 'MAT-101'],
        ];

        $mCivil = [
            ['nombre' => 'Física General', 'sigla' => 'FIS-100'],
            ['nombre' => 'Cálculo I', 'sigla' => 'MAT-102'],
        ];

        $mMedicina = [
            ['nombre' => 'Anatomía Humana', 'sigla' => 'MED-101'],
            ['nombre' => 'Histología', 'sigla' => 'MED-102'],
        ];

        $materiasList = [];

        foreach ($mSistemas as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $sis->id]));
        }
        foreach ($mCivil as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $civ->id]));
        }
        foreach ($mMedicina as $m) {
            $materiasList[] = Materia::create(array_merge($m, ['carrera_id' => $med->id]));
        }

        // 6. Crear Docentes y asociar disponibilidad
        $docentesData = [
            ['name' => 'Carlos Mendoza', 'email' => 'carlos@cup.edu.bo', 'esp' => 'Programación y Algoritmos', 'materias' => ['SIS-110', 'MAT-101']],
            ['name' => 'María Delgadillo', 'email' => 'maria@cup.edu.bo', 'esp' => 'Matemáticas y Cálculo', 'materias' => ['MAT-101', 'MAT-102']],
            ['name' => 'Jorge Vaca', 'email' => 'jorge@cup.edu.bo', 'esp' => 'Física y Mecánica', 'materias' => ['FIS-100']],
            ['name' => 'Dra. Elena Prado', 'email' => 'elena@cup.edu.bo', 'esp' => 'Anatomía y Patología', 'materias' => ['MED-101', 'MED-102']],
            ['name' => 'Dr. René Justiniano', 'email' => 'rene@cup.edu.bo', 'esp' => 'Histología y Citología', 'materias' => ['MED-102']],
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
        $carrCIV = $carreras[1];
        $carrMED = $carreras[2];

        // Crear postulantes con distribución: SIS 50%, MED 30%, CIV 20%
        for ($i = 1; $i <= $numPostulantes; $i++) {
            $rand = rand(1, 100);
            if ($rand <= 50) {
                $primera = $carrSIS;
                $segunda = rand(1, 2) == 1 ? $carrCIV : null;
            } elseif ($rand <= 80) {
                $primera = $carrMED;
                $segunda = null;
            } else {
                $primera = $carrCIV;
                $segunda = rand(1, 2) == 1 ? $carrSIS : null;
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
