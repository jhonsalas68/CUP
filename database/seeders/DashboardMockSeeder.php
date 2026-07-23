<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Docente;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardMockSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Limpieza de tablas con RESTART IDENTITY CASCADE
            DB::statement('TRUNCATE TABLE notas RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE examenes RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE horarios RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE postulante_grupo RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE asignaciones_docente RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE grupos RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE docente_materia RESTART IDENTITY CASCADE;');

            // Eliminar usuarios que no sean admin o coordinador
            User::whereNotIn('email', ['admin@cup.edu.bo', 'coordinador@cup.edu.bo'])->forceDelete();

            DB::statement('TRUNCATE TABLE postulantes RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE docentes RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE cupos RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE materias RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE carreras RESTART IDENTITY CASCADE;');
            DB::statement('TRUNCATE TABLE gestiones RESTART IDENTITY CASCADE;');

            // Asegurar que existan los roles
            $this->call(RolesAndPermissionsSeeder::class);

            // 2. Crear Gestiones
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

            $sis = Carrera::create(['nombre' => 'Ingeniería de Sistemas', 'sigla' => 'SIS']);
            $inf = Carrera::create(['nombre' => 'Ingeniería Informática', 'sigla' => 'INF']);
            $red = Carrera::create(['nombre' => 'Ingeniería en Redes y Telecomunicaciones', 'sigla' => 'RED']);
            $rob = Carrera::create(['nombre' => 'Ingeniería Robótica', 'sigla' => 'ROB']);

            $carreras = [$sis, $inf, $red, $rob];

            // 4. Configurar Cupos por gestión
            $cuposData = [
                'I-2025' => [
                    'SIS' => [30, 15], 'INF' => [20, 10], 'RED' => [15, 5], 'ROB' => [12, 6],
                ],
                'II-2025' => [
                    'SIS' => [35, 15], 'INF' => [25, 10], 'RED' => [18, 5], 'ROB' => [15, 7],
                ],
                'I-2026' => [
                    'SIS' => [150, 50], 'INF' => [150, 50], 'RED' => [150, 50], 'ROB' => [150, 50],
                ],
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

            foreach ($mSistemas as $m) {
                Materia::create(array_merge($m, ['carrera_id' => $sis->id]));
            }
            foreach ($mInformatica as $m) {
                Materia::create(array_merge($m, ['carrera_id' => $inf->id]));
            }
            foreach ($mRedes as $m) {
                Materia::create(array_merge($m, ['carrera_id' => $red->id]));
            }
            foreach ($mRobotica as $m) {
                Materia::create(array_merge($m, ['carrera_id' => $rob->id]));
            }

            // 6. Crear Docentes
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
                    'ci' => '777888'.$dIdx,
                    'telefono' => '7891234'.$dIdx,
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

            // Actualizar la secuencia de docentes
            $maxDocenteId = DB::table('docentes')->max('id');
            if ($maxDocenteId) {
                DB::statement("SELECT setval('docentes_id_seq', ?)", [$maxDocenteId]);
            }

            // 7. Iniciar la inserción masiva de postulantes por gestión
            $startUserId = DB::table('users')->max('id') + 1;
            $startPostulanteId = 1;

            $nextIds = $this->seedHistoricalGestion($g1, 1000, $carreras, $startUserId, $startPostulanteId);
            $startUserId = $nextIds['userId'];
            $startPostulanteId = $nextIds['postulanteId'];

            $nextIds = $this->seedHistoricalGestion($g2, 1000, $carreras, $startUserId, $startPostulanteId);
            $startUserId = $nextIds['userId'];
            $startPostulanteId = $nextIds['postulanteId'];

            $this->seedHistoricalGestion($gActive, 1000, $carreras, $startUserId, $startPostulanteId);

            // Actualizar secuencias de users y postulantes al final de todo
            $maxUserId = DB::table('users')->max('id');
            if ($maxUserId) {
                DB::statement("SELECT setval('users_id_seq', ?)", [$maxUserId]);
            }
            $maxPostulanteId = DB::table('postulantes')->max('id');
            if ($maxPostulanteId) {
                DB::statement("SELECT setval('postulantes_id_seq', ?)", [$maxPostulanteId]);
            }
        });
    }

    private function seedHistoricalGestion(
        Gestion $g,
        int $numPostulantes,
        array $carreras,
        int $startUserId,
        int $startPostulanteId
    ): array {
        $carrSIS = $carreras[0];
        $carrINF = $carreras[1];
        $carrRED = $carreras[2];
        $carrROB = $carreras[3];

        echo "Bulk seeding historical gestion: {$g->nombre} (ID: {$g->id})...\n";

        $firstNames = ['Juan', 'Pedro', 'María', 'Ana', 'Luis', 'Carlos', 'José', 'Sofía', 'Laura', 'Miguel', 'David', 'Elena', 'Diego', 'Lucía', 'Alejandro', 'Gabriel', 'Daniel', 'Valentina', 'Camila', 'Mateo', 'Lucas', 'Santiago', 'Andrés', 'Fernanda', 'Isabella', 'Martín', 'Javier', 'Carmen', 'Patricia', 'Roberto', 'Paola', 'Gustavo', 'Beatriz', 'Raúl', 'Adriana'];
        $lastNames = ['Gómez', 'Rodríguez', 'Pérez', 'López', 'González', 'Martínez', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Díaz', 'Cruz', 'Ortiz', 'Gutiérrez', 'Chávez', 'Alvarez', 'Ruiz', 'Vargas', 'Mendoza', 'Rojas', 'Castillo', 'Silva', 'Morales', 'Herrera', 'Medina', 'Castro', 'Muñoz', 'Ramos', 'Guzmán', 'Salas', 'Suárez', 'Pinto', 'Aguilar', 'Romero'];

        $passwordHash = Hash::make('password');
        $postulanteRoleId = DB::table('roles')->where('name', 'Postulante')->value('id') ?? 4;

        $usersData = [];
        $modelHasRolesData = [];
        $postulantesData = [];

        // Generar postulantes en memoria
        $postulanteList = []; // Para ranking
        for ($i = 1; $i <= $numPostulantes; $i++) {
            $userId = $startUserId + $i - 1;
            $postulanteId = $startPostulanteId + $i - 1;

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
            $segunda = ($primera->id !== $carrINF->id) ? $carrINF : $carrSIS;

            $first = $firstNames[($i + $g->id) % count($firstNames)];
            $last1 = $lastNames[($i * 3) % count($lastNames)];
            $last2 = $lastNames[($i * 7) % count($lastNames)];
            $name = "$first $last1 $last2";

            $usersData[] = [
                'id' => $userId,
                'name' => $name,
                'email' => "postulante_{$g->nombre}_{$i}@cup.edu.bo",
                'password' => $passwordHash,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $modelHasRolesData[] = [
                'role_id' => $postulanteRoleId,
                'model_type' => 'App\Models\User',
                'model_id' => $userId,
            ];

            $postulantesData[$postulanteId] = [
                'id' => $postulanteId,
                'user_id' => $userId,
                'nombres_apellidos' => $name,
                'ci' => 1000000 + ($g->id * 100000) + $i,
                'telefono' => 60000000 + ($g->id * 100000) + $i,
                'fecha_nacimiento' => today()->subYears(rand(17, 22))->format('Y-m-d'),
                'sexo' => rand(1, 100) > 50 ? 'Masculino' : 'Femenino',
                'direccion' => 'Barrio Lindo Calle '.rand(1, 20),
                'colegio_procedencia' => 'Colegio Nacional '.($i % 3 === 0 ? 'A' : 'B'),
                'ciudad' => 'Santa Cruz',
                'carrera_primera_opcion_id' => $primera->id,
                'carrera_segunda_opcion_id' => $segunda->id,
                'gestion_id' => $g->id,
                'estado_admision' => 'pendiente', // Se actualizará luego del proceso en memoria
                'nota_final' => 0.00,             // Se actualizará luego del proceso en memoria
                'ci_vigente' => true,
                'titulo_bachiller' => true,
                'libreta_legalizada' => true,
                'habilitado' => true,
                'pago_realizado' => true,
                'pago_matricula_realizado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $postulanteList[] = &$postulantesData[$postulanteId];
        }

        // Crear exámenes en esta gestión en memoria e insertarlos
        $startExamenId = DB::table('examenes')->max('id') + 1;
        if (! $startExamenId) {
            $startExamenId = 1;
        }

        $examIndex = 0;
        $examenesData = [];
        $examMap = []; // $examMap[$materiaId][$examName] = $examId

        $materias = Materia::all();
        foreach ($materias as $m) {
            foreach (['Primer Parcial' => 30.00, 'Segundo Parcial' => 30.00, 'Examen Final' => 40.00] as $nombre => $ponderacion) {
                $examId = $startExamenId + $examIndex;
                $examenesData[] = [
                    'id' => $examId,
                    'nombre' => $nombre,
                    'materia_id' => $m->id,
                    'gestion_id' => $g->id,
                    'ponderacion' => $ponderacion,
                    'fecha' => today()->addDays($nombre === 'Primer Parcial' ? 10 : ($nombre === 'Segundo Parcial' ? 20 : 30))->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $examMap[$m->id][$nombre] = $examId;
                $examIndex++;
            }
        }
        DB::table('examenes')->insert($examenesData);
        DB::statement("SELECT setval('examenes_id_seq', ?)", [DB::table('examenes')->max('id')]);

        // Calificar a los postulantes y calcular nota_final en memoria
        $notasData = [];

        $materiasByCarrera = Materia::all()->groupBy('carrera_id');

        foreach ($postulanteList as &$p) {
            $cId = $p['carrera_primera_opcion_id'];
            $mats = $materiasByCarrera->get($cId) ?? collect();

            $sumMaterias = 0.00;

            foreach ($mats as $m) {
                $esAprobado = rand(1, 100) <= 75;
                if ($esAprobado) {
                    $n1 = rand(60, 100);
                    $n2 = rand(60, 100);
                    $n3 = rand(60, 100);
                } else {
                    $n1 = rand(30, 70);
                    $n2 = rand(30, 70);
                    $n3 = rand(20, 58);
                }

                $notaMateria = ($n1 * 0.3) + ($n2 * 0.3) + ($n3 * 0.4);
                $sumMaterias += $notaMateria;

                $notasData[] = [
                    'postulante_id' => $p['id'],
                    'examen_id' => $examMap[$m->id]['Primer Parcial'],
                    'puntaje' => $n1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $notasData[] = [
                    'postulante_id' => $p['id'],
                    'examen_id' => $examMap[$m->id]['Segundo Parcial'],
                    'puntaje' => $n2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $notasData[] = [
                    'postulante_id' => $p['id'],
                    'examen_id' => $examMap[$m->id]['Examen Final'],
                    'puntaje' => $n3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $promedio = round($sumMaterias / $mats->count(), 2);
            $p['nota_final'] = $promedio;
            $p['estado_admision'] = $promedio >= 60.00 ? 'pendiente' : 'reprobado';
        }
        unset($p); // romper la referencia

        // Ordenar todos los aprobados de mayor a menor nota_final en memoria
        $aprobadosList = [];
        foreach ($postulanteList as &$p) {
            if ($p['estado_admision'] === 'pendiente') {
                $aprobadosList[] = &$p;
            }
        }
        unset($p);

        usort($aprobadosList, function ($a, $b) {
            if ($b['nota_final'] === $a['nota_final']) {
                return $a['id'] <=> $b['id'];
            }
            return $b['nota_final'] <=> $a['nota_final'];
        });

        // Inicializar capacidades
        $capacidades1ra = [];
        $capacidades2da = [];
        $capacidadesTotal = [];
        $admitidos1raCounts = [];
        $admitidos2daCounts = [];
        $admitidosTotalCounts = [];

        $cupos = Cupo::where('gestion_id', $g->id)->get()->keyBy('carrera_id');

        foreach ($carreras as $carrera) {
            $cupoObj = $cupos->get($carrera->id);
            $cap1 = $cupoObj ? $cupoObj->cantidad_primera_opcion : 150;
            $cap2 = $cupoObj ? $cupoObj->cantidad_segunda_opcion : 50;

            $capacidades1ra[$carrera->id] = $cap1;
            $capacidades2da[$carrera->id] = $cap2;
            $capacidadesTotal[$carrera->id] = $cap1 + $cap2;

            $admitidos1raCounts[$carrera->id] = 0;
            $admitidos2daCounts[$carrera->id] = 0;
            $admitidosTotalCounts[$carrera->id] = 0;
        }

        foreach ($aprobadosList as &$postulante) {
            $c1 = $postulante['carrera_primera_opcion_id'];
            $c2 = $postulante['carrera_segunda_opcion_id'];

            // Intentar primera opción
            if ($c1 && isset($capacidades1ra[$c1]) && $admitidos1raCounts[$c1] < $capacidades1ra[$c1] && $admitidosTotalCounts[$c1] < $capacidadesTotal[$c1]) {
                $postulante['estado_admision'] = 'admitido_primera_opcion';
                $admitidos1raCounts[$c1]++;
                $admitidosTotalCounts[$c1]++;
            }
            // Intentar segunda opción
            elseif ($c2 && isset($capacidades2da[$c2]) && $admitidos2daCounts[$c2] < $capacidades2da[$c2] && $admitidosTotalCounts[$c2] < $capacidadesTotal[$c2]) {
                $postulante['estado_admision'] = 'admitido_segunda_opcion';
                $admitidos2daCounts[$c2]++;
                $admitidosTotalCounts[$c2]++;
            }
            // No admitido
            else {
                $postulante['estado_admision'] = 'no_admitido';
            }
        }
        unset($postulante);

        // Insertar usuarios, roles y postulantes
        $usersChunks = array_chunk($usersData, 500);
        foreach ($usersChunks as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $rolesChunks = array_chunk($modelHasRolesData, 500);
        foreach ($rolesChunks as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }

        $postulantesChunks = array_chunk(array_values($postulantesData), 500);
        foreach ($postulantesChunks as $chunk) {
            DB::table('postulantes')->insert($chunk);
        }

        // Insertar notas
        $notasChunks = array_chunk($notasData, 1000);
        foreach ($notasChunks as $chunk) {
            DB::table('notas')->insert($chunk);
        }

        // Generar grupos y horarios en memoria
        $startGrupoId = DB::table('grupos')->max('id') + 1;
        if (! $startGrupoId) {
            $startGrupoId = 1;
        }

        $gruposData = [];
        $postulanteGrupoData = [];
        $asignacionesDocenteData = [];
        $horariosData = [];

        $grupoIndex = 0;

        // Fetch docentes map per materia ID
        $docentesRaw = DB::table('docente_materia')
            ->select('materia_id', 'docente_id')
            ->get();
        $docenteMateriaMap = [];
        foreach ($docentesRaw as $dr) {
            $docenteMateriaMap[$dr->materia_id][] = $dr->docente_id;
        }

        // Dividir los postulantes en grupos y crear horarios
        foreach ($carreras as $c) {
            // Obtener todos los postulantes de esta carrera (primera opción)
            $cPostulantes = array_filter($postulantesData, function ($p) use ($c) {
                return $p['carrera_primera_opcion_id'] === $c->id;
            });
            $cPostulantes = array_values($cPostulantes);
            $totalInscritos = count($cPostulantes);

            if ($totalInscritos === 0) {
                continue;
            }

            $mats = Materia::where('carrera_id', $c->id)->get();

            foreach ($mats as $mIdx => $materia) {
                $numGrupos = (int) ceil($totalInscritos / 70);

                // Distribuir equitativamente
                $baseSize = (int) floor($totalInscritos / $numGrupos);
                $remainder = $totalInscritos % $numGrupos;

                $offset = 0;
                for ($gIdx = 0; $gIdx < $numGrupos; $gIdx++) {
                    $take = $baseSize + ($gIdx < $remainder ? 1 : 0);
                    $grupoPostulantes = array_slice($cPostulantes, $offset, $take);
                    $offset += $take;

                    $grupoId = $startGrupoId + $grupoIndex;
                    $grupoNombre = "{$materia->sigla} - G".($gIdx + 1);

                    $gruposData[] = [
                        'id' => $grupoId,
                        'nombre' => $grupoNombre,
                        'materia_id' => $materia->id,
                        'gestion_id' => $g->id,
                        'cupo_maximo' => 70,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Postulantes asociados
                    foreach ($grupoPostulantes as $gp) {
                        $postulanteGrupoData[] = [
                            'postulante_id' => $gp['id'],
                            'grupo_id' => $grupoId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Docente calificado
                    $mDocentes = $docenteMateriaMap[$materia->id] ?? [1];
                    $docenteElegido = $mDocentes[$gIdx % count($mDocentes)];

                    $asignacionesDocenteData[] = [
                        'docente_id' => $docenteElegido,
                        'grupo_id' => $grupoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Horario
                    $slotIndex = ($mIdx + $gIdx) % 8;
                    $slotKey = 'slot_'.($slotIndex + 1);
                    $slotsPool = [
                        'slot_1' => ['dias' => ['lunes', 'miercoles'], 'inicio' => '07:30:00', 'fin' => '09:00:00'],
                        'slot_2' => ['dias' => ['lunes', 'miercoles'], 'inicio' => '09:15:00', 'fin' => '10:45:00'],
                        'slot_3' => ['dias' => ['lunes', 'miercoles'], 'inicio' => '11:00:00', 'fin' => '12:30:00'],
                        'slot_4' => ['dias' => ['martes', 'jueves'], 'inicio' => '07:30:00', 'fin' => '09:00:00'],
                        'slot_5' => ['dias' => ['martes', 'jueves'], 'inicio' => '09:15:00', 'fin' => '10:45:00'],
                        'slot_6' => ['dias' => ['martes', 'jueves'], 'inicio' => '11:00:00', 'fin' => '12:30:00'],
                        'slot_7' => ['dias' => ['viernes', 'sabado'], 'inicio' => '07:30:00', 'fin' => '09:00:00'],
                        'slot_8' => ['dias' => ['viernes', 'sabado'], 'inicio' => '09:15:00', 'fin' => '10:45:00'],
                    ];
                    $slot = $slotsPool[$slotKey];
                    $aulaIndex = ($grupoIndex) % 9;
                    $aulasPool = ['Aula 101', 'Aula 102', 'Aula 103', 'Aula 201', 'Aula 202', 'Aula 203', 'Lab de Computación A', 'Lab de Computación B', 'Auditorio Civil'];
                    $aula = $aulasPool[$aulaIndex];

                    foreach ($slot['dias'] as $dia) {
                        $horariosData[] = [
                            'grupo_id' => $grupoId,
                            'dia_semana' => $dia,
                            'hora_inicio' => $slot['inicio'],
                            'hora_fin' => $slot['fin'],
                            'aula' => $aula,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $grupoIndex++;
                }
            }
        }

        // Insertar grupos, horarios y asignaciones
        if (! empty($gruposData)) {
            DB::table('grupos')->insert($gruposData);
            DB::statement("SELECT setval('grupos_id_seq', ?)", [DB::table('grupos')->max('id')]);
        }
        if (! empty($postulanteGrupoData)) {
            $chunkSize = 1000;
            foreach (array_chunk($postulanteGrupoData, $chunkSize) as $chunk) {
                DB::table('postulante_grupo')->insert($chunk);
            }
        }
        if (! empty($asignacionesDocenteData)) {
            DB::table('asignaciones_docente')->insert($asignacionesDocenteData);
        }
        if (! empty($horariosData)) {
            $chunkSize = 1000;
            foreach (array_chunk($horariosData, $chunkSize) as $chunk) {
                DB::table('horarios')->insert($chunk);
            }
        }

        return [
            'userId' => $startUserId + $numPostulantes,
            'postulanteId' => $startPostulanteId + $numPostulantes,
        ];
    }
}
