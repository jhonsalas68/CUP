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
            
            // Computación adicionales
            ['name' => 'Ing. Ana Colque', 'email' => 'ana.colque@cup.edu.bo', 'esp' => 'Computación y Sistemas', 'materias' => ['COM-SIS', 'COM-INF', 'COM-RED', 'COM-ROB']],
            ['name' => 'Lic. Roberto Mamani', 'email' => 'roberto.mamani@cup.edu.bo', 'esp' => 'Desarrollo de Software', 'materias' => ['COM-SIS', 'COM-INF', 'COM-RED', 'COM-ROB']],
            ['name' => 'Ing. Luis Choque', 'email' => 'luis.choque@cup.edu.bo', 'esp' => 'Ingeniería de Sistemas', 'materias' => ['COM-SIS', 'COM-INF', 'COM-RED', 'COM-ROB']],
            ['name' => 'Ing. Gabriel Flores', 'email' => 'gabriel.flores@cup.edu.bo', 'esp' => 'Sistemas y Redes', 'materias' => ['COM-SIS', 'COM-INF', 'COM-RED', 'COM-ROB']],
            
            // Matemáticas adicionales
            ['name' => 'Lic. Silvia Flores', 'email' => 'silvia.flores@cup.edu.bo', 'esp' => 'Álgebra y Cálculo', 'materias' => ['MAT-SIS', 'MAT-INF', 'MAT-RED', 'MAT-ROB']],
            ['name' => 'Dr. Hugo Flores', 'email' => 'hugo.flores@cup.edu.bo', 'esp' => 'Matemáticas Puras', 'materias' => ['MAT-SIS', 'MAT-INF', 'MAT-RED', 'MAT-ROB']],
            ['name' => 'Msc. Ramiro Quispe', 'email' => 'ramiro.quispe@cup.edu.bo', 'esp' => 'Estadística y Probabilidades', 'materias' => ['MAT-SIS', 'MAT-INF', 'MAT-RED', 'MAT-ROB']],
            ['name' => 'Msc. Carlos Rojas', 'email' => 'carlos.rojas@cup.edu.bo', 'esp' => 'Matemáticas Aplicadas', 'materias' => ['MAT-SIS', 'MAT-INF', 'MAT-RED', 'MAT-ROB']],
            
            // Física adicionales
            ['name' => 'Lic. Patricia Aguilar', 'email' => 'patricia.aguilar@cup.edu.bo', 'esp' => 'Física General y Mecánica', 'materias' => ['FIS-SIS', 'FIS-INF', 'FIS-RED', 'FIS-ROB']],
            ['name' => 'Ing. Marcos Pinto', 'email' => 'marcos.pinto@cup.edu.bo', 'esp' => 'Física Aplicada', 'materias' => ['FIS-SIS', 'FIS-INF', 'FIS-RED', 'FIS-ROB']],
            ['name' => 'Dr. Fernando Vargas', 'email' => 'fernando.vargas@cup.edu.bo', 'esp' => 'Mecánica Cuántica y Ondas', 'materias' => ['FIS-SIS', 'FIS-INF', 'FIS-RED', 'FIS-ROB']],
            ['name' => 'Lic. Jorge Ortiz', 'email' => 'jorge.ortiz@cup.edu.bo', 'esp' => 'Física e Ingeniería', 'materias' => ['FIS-SIS', 'FIS-INF', 'FIS-RED', 'FIS-ROB']],
            
            // Inglés adicionales
            ['name' => 'Msc. Claudia Justiniano', 'email' => 'claudia.justiniano@cup.edu.bo', 'esp' => 'Inglés Técnico', 'materias' => ['ING-SIS', 'ING-INF', 'ING-RED', 'ING-ROB']],
            ['name' => 'Lic. Gabriela Cortez', 'email' => 'gabriela.cortez@cup.edu.bo', 'esp' => 'Idiomas y Lingüística', 'materias' => ['ING-SIS', 'ING-INF', 'ING-RED', 'ING-ROB']],
            ['name' => 'Ing. David Orellana', 'email' => 'david.orellana@cup.edu.bo', 'esp' => 'Traducción Técnica', 'materias' => ['ING-SIS', 'ING-INF', 'ING-RED', 'ING-ROB']],
            ['name' => 'Dr. Oscar Ruiz', 'email' => 'oscar.ruiz@cup.edu.bo', 'esp' => 'Idiomas y Comunicación', 'materias' => ['ING-SIS', 'ING-INF', 'ING-RED', 'ING-ROB']],
        ];

        $passwordHash = Hash::make('password');
        $docentesList = [];
        foreach ($docentesData as $dIdx => $d) {
            $u = User::create([
                'name' => $d['name'],
                'email' => $d['email'],
                'password' => $passwordHash,
            ]);
            $u->assignRole('Docente');

            $doc = Docente::create([
                'user_id' => $u->id,
                'nombre' => $d['name'],
                'ci' => '777888' . $dIdx,
                'telefono' => '7891234' . $dIdx,
                'especialidad' => $d['esp'],
                'disponibilidad_horaria' => ['slot_1', 'slot_2', 'slot_3', 'slot_4', 'slot_5', 'slot_6', 'slot_7', 'slot_8'],
                'formacion_academica' => 'Maestría en Educación Superior y Licenciatura en la Especialidad.',
                'profesional_area' => true,
                'tiene_maestria' => true,
                'tiene_diplomado' => true,
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
        $this->seedHistoricalGestion($g1, 350, $carreras, $groupService, $examService, $admissionService);
        $this->seedHistoricalGestion($g2, 350, $carreras, $groupService, $examService, $admissionService);

        // 9. Poblar Gestión Activa (I-2026) con postulantes y notas
        $this->seedHistoricalGestion($gActive, 900, $carreras, $groupService, $examService, $admissionService);
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

        $firstNames = ['Juan', 'Pedro', 'María', 'Ana', 'Luis', 'Carlos', 'José', 'Sofía', 'Laura', 'Miguel', 'David', 'Elena', 'Diego', 'Lucía', 'Alejandro', 'Gabriel', 'Daniel', 'Valentina', 'Camila', 'Mateo', 'Lucas', 'Santiago', 'Andrés', 'Fernanda', 'Isabella', 'Martín', 'Javier', 'Carmen', 'Patricia', 'Roberto', 'Paola', 'Gustavo', 'Beatriz', 'Raúl', 'Adriana'];
        $lastNames = ['Gómez', 'Rodríguez', 'Pérez', 'López', 'González', 'Martínez', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Díaz', 'Cruz', 'Ortiz', 'Gutiérrez', 'Chávez', 'Alvarez', 'Ruiz', 'Vargas', 'Mendoza', 'Rojas', 'Castillo', 'Silva', 'Morales', 'Herrera', 'Medina', 'Castro', 'Muñoz', 'Ramos', 'Guzmán', 'Salas', 'Suárez', 'Pinto', 'Aguilar', 'Romero'];

        $passwordHash = Hash::make('password');

        // Crear postulantes con distribución: SIS 40%, RED 20%, INF 20%, ROB 20%
        for ($i = 1; $i <= $numPostulantes; $i++) {
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $primera = $carrSIS;
            } elseif ($rand <= 60) {
                $primera = $carrRED;
            } elseif ($rand <= 80) {
                $primera = $carrINF;
            } else {
                $primera = $carrROB;
            }
            // Assign a second option different from first; default to Informatica, or Sistema if first is Informatica
            $segunda = ($primera->id !== $carrINF->id) ? $carrINF : $carrSIS;


            $first = $firstNames[($i + $g->id) % count($firstNames)];
            $last1 = $lastNames[($i * 3) % count($lastNames)];
            $last2 = $lastNames[($i * 7) % count($lastNames)];
            $name = "$first $last1 $last2";

            $u = User::create([
                'name' => $name,
                'email' => "postulante_{$g->nombre}_{$i}@cup.edu.bo",
                'password' => $passwordHash,
            ]);
            $u->assignRole('Postulante');

            Postulante::create([
                'user_id' => $u->id,
                'nombres_apellidos' => $name,
                'ci' => 1000000 + ($g->id * 100000) + $i,
                'telefono' => 60000000 + ($g->id * 100000) + $i,
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
