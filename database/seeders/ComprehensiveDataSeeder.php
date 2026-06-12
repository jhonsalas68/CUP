<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\User;
use App\Services\GroupGenerationService;
use App\Services\ExamService;
use App\Services\AdmissionSelectionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class ComprehensiveDataSeeder extends Seeder
{
    private const POSTULANTES_COUNT = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando población de datos comprensiva...');

        // 1. Limpieza de tablas transaccionales y maestras para asegurar consistencia
        $this->command->info('Limpiando tablas existentes...');
        DB::statement('TRUNCATE TABLE notas CASCADE;');
        DB::statement('TRUNCATE TABLE examenes CASCADE;');
        DB::statement('TRUNCATE TABLE horarios CASCADE;');
        DB::statement('TRUNCATE TABLE postulante_grupo CASCADE;');
        DB::statement('TRUNCATE TABLE asignaciones_docente CASCADE;');
        DB::statement('TRUNCATE TABLE grupos CASCADE;');
        DB::statement('TRUNCATE TABLE docente_materia CASCADE;');
        
        User::role(['Docente', 'Postulante'])->forceDelete();
        
        DB::statement('TRUNCATE TABLE postulantes CASCADE;');
        DB::statement('TRUNCATE TABLE docentes CASCADE;');
        DB::statement('TRUNCATE TABLE cupos CASCADE;');
        DB::statement('TRUNCATE TABLE materias CASCADE;');
        DB::statement('TRUNCATE TABLE carreras CASCADE;');
        DB::statement('TRUNCATE TABLE gestiones CASCADE;');

        // 2. Recrear roles y permisos, carreras y materias
        $this->command->info('Creando carreras y materias base...');
        $this->call([
            RolesAndPermissionsSeeder::class,
            CarreraSeeder::class,
            MateriaSeeder::class,
        ]);

        // 3. Crear Gestión Activa de Prueba (I-2026)
        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carreras = Carrera::all();
        $materias = Materia::all();

        // 4. Configurar Cupos para las Carreras en la Gestión I-2026
        // Establecemos cupos moderados para asegurar que algunos postulantes aprobados
        // entren en la primera opción, otros en la segunda, y algunos queden como no admitidos.
        $this->command->info('Configurando cupos...');
        foreach ($carreras as $carrera) {
            Cupo::create([
                'carrera_id' => $carrera->id,
                'gestion_id' => $gestion->id,
                'cantidad_primera_opcion' => 80,
                'cantidad_segunda_opcion' => 45,
            ]);
        }

        // 5. Crear usuarios docentes calificados
        // Necesitamos al menos 20 docentes para cubrir las clases sin exceder la carga máxima de 4 grupos por docente
        $this->command->info('Creando docentes calificados...');
        $faker = Faker::create('es_ES');
        $passwordHash = Hash::make('password');
        $docentes = collect();

        for ($i = 1; $i <= 20; $i++) {
            $nombre = $faker->firstName() . ' ' . $faker->lastName() . ' ' . $faker->lastName();
            $email = 'docente' . $i . '@cup.edu.bo';

            $user = User::create([
                'name' => $nombre,
                'email' => $email,
                'password' => $passwordHash,
            ]);
            $user->assignRole('Docente');

            $docente = Docente::create([
                'user_id' => $user->id,
                'nombre' => $nombre,
                'ci' => $faker->numerify('########'),
                'telefono' => $faker->numerify('7#######'),
                'especialidad' => $faker->randomElement([
                    'Matemáticas Aplicadas',
                    'Física e Ingeniería',
                    'Programación y Algoritmos',
                    'Sistemas y Redes',
                    'Idiomas y Comunicación'
                ]),
                'disponibilidad_horaria' => ['slot_1', 'slot_2', 'slot_3', 'slot_4', 'slot_5', 'slot_6', 'slot_7', 'slot_8'],
                'formacion_academica' => 'Maestría en Educación Superior y Licenciatura en la Especialidad.',
                'profesional_area' => true,
                'tiene_maestria' => true,
                'tiene_diplomado' => true,
            ]);

            // Asociar el docente a todas las materias para maximizar compatibilidad en la asignación automática
            $docente->materias()->attach($materias->pluck('id'));
            $docentes->push($docente);
        }

        // 6. Crear 1000 postulantes de prueba
        // Se distribuyen en las 4 carreras de forma equitativa (~250 por carrera)
        $this->command->info('Creando 1000 postulantes...');

        for ($i = 1; $i <= self::POSTULANTES_COUNT; $i++) {
            // Rotar primera opción de carrera
            $carreraPrimera = $carreras[$i % $carreras->count()];
            
            // Elegir segunda opción que sea diferente de la primera
            $carrerasFiltradas = $carreras->filter(fn($c) => $c->id !== $carreraPrimera->id)->values();
            $carreraSegunda = $carrerasFiltradas[$i % $carrerasFiltradas->count()];

            $nombre = $faker->firstName() . ' ' . $faker->lastName() . ' ' . $faker->lastName();
            $email = 'postulante' . $i . '@cup.edu.bo';

            $user = User::create([
                'name' => $nombre,
                'email' => $email,
                'password' => $passwordHash,
            ]);
            $user->assignRole('Postulante');

            Postulante::create([
                'user_id' => $user->id,
                'nombres_apellidos' => $nombre,
                'ci' => $faker->unique()->numerify('########'),
                'telefono' => $faker->numerify('6#######'),
                'fecha_nacimiento' => $faker->dateTimeBetween('-24 years', '-17 years')->format('Y-m-d'),
                'sexo' => $faker->randomElement(['M', 'F']),
                'direccion' => $faker->address(),
                'colegio_procedencia' => $faker->randomElement([
                    'Colegio Nacional Florida',
                    'Colegio La Salle',
                    'U.E. Marista',
                    'U.E. Fe y Alegría',
                    'U.E. Don Bosco'
                ]),
                'ciudad' => $faker->randomElement(['Santa Cruz', 'La Paz', 'Cochabamba', 'Oruro', 'Sucre']),
                'carrera_primera_opcion_id' => $carreraPrimera->id,
                'carrera_segunda_opcion_id' => $carreraSegunda->id,
                'gestion_id' => $gestion->id,
                'estado_admision' => 'pendiente',
                'ci_vigente' => true,
                'titulo_bachiller' => true,
                'libreta_legalizada' => true,
            ]);

            if ($i % 200 === 0) {
                $this->command->line("  → Creados $i postulantes...");
            }
        }

        // 7. Generar grupos académicos automáticamente
        // Esto distribuye a los 1000 estudiantes equitativamente en grupos por materia de su 1ra opción.
        // También asigna docente calificado, aula y slot horario.
        $this->command->info('Generando grupos académicos de forma consistente...');
        $groupService = new GroupGenerationService();
        $groupService->generate($gestion->id);

        // Verificar que no existan grupos sin alumnos
        $gruposCreados = Grupo::where('gestion_id', $gestion->id)->get();
        foreach ($gruposCreados as $grupo) {
            if ($grupo->postulantes()->count() === 0) {
                $this->command->warn("Advertencia: Grupo vacío detectado: " . $grupo->nombre);
            }
        }
        $this->command->info('✓ Grupos académicos generados: ' . $gruposCreados->count());

        // 8. Crear exámenes oficiales para todas las materias de esta gestión
        // Seguimos la regla de ponderación 30 / 30 / 40.
        $this->command->info('Registrando exámenes por materia...');
        $examService = new ExamService();
        $examMap = []; // $examMap[$materiaId][nombre] = examenModel

        foreach ($materias as $m) {
            $examMap[$m->id]['Primer Parcial'] = $examService->createExam($m->id, $gestion->id, 'Primer Parcial', now()->addDays(10)->format('Y-m-d'));
            $examMap[$m->id]['Segundo Parcial'] = $examService->createExam($m->id, $gestion->id, 'Segundo Parcial', now()->addDays(20)->format('Y-m-d'));
            $examMap[$m->id]['Examen Final'] = $examService->createExam($m->id, $gestion->id, 'Examen Final', now()->addDays(30)->format('Y-m-d'));
        }

        // 9. Calificar a todos los postulantes de forma realista
        // Un ~65% de postulantes aprobará académicamente y el otro ~35% reprobará.
        $this->command->info('Asignando notas a postulantes...');
        $postulantes = Postulante::where('gestion_id', $gestion->id)->get();
        
        // Creamos una cuenta de admin predeterminada si no existe para la auditoría de registro de notas
        $admin = User::role('Administrador')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrador General',
                'email' => 'admin@cup.edu.bo',
                'password' => $passwordHash,
            ]);
            $admin->assignRole('Administrador');
        }

        foreach ($postulantes as $idx => $p) {
            $carreraId = $p->carrera_primera_opcion_id;
            $mats = Materia::where('carrera_id', $carreraId)->get();

            // Decidir rendimiento académico
            // 65% de probabilidad de aprobar todas las materias del semestre
            $esAprobadoAcademico = ($idx % 100) < 65;

            foreach ($mats as $m) {
                if ($esAprobadoAcademico) {
                    // Si aprueba, todas las notas de los exámenes de la materia le darán promedio >= 60.
                    $n1 = rand(65, 100);
                    $n2 = rand(65, 100);
                    $n3 = rand(60, 100);
                } else {
                    // Si reprueba, generamos notas que lo hagan reprobar (una de las materias con promedio < 60)
                    // Para que sea variado, podemos hacer que repruebe solo algunas materias.
                    // Si es la primera materia de su plan, la reprobamos definitivamente.
                    if ($m->id === $mats->first()->id) {
                        $n1 = rand(20, 50);
                        $n2 = rand(25, 50);
                        $n3 = rand(20, 55); // Promedio final materia garantizado < 60
                    } else {
                        // Las otras materias las puede pasar o no
                        $n1 = rand(50, 80);
                        $n2 = rand(50, 80);
                        $n3 = rand(50, 80);
                    }
                }

                $exam1 = $examMap[$m->id]['Primer Parcial'];
                $exam2 = $examMap[$m->id]['Segundo Parcial'];
                $exam3 = $examMap[$m->id]['Examen Final'];

                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam1->id, 'puntaje' => $n1, 'registrado_por' => $admin->id]);
                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam2->id, 'puntaje' => $n2, 'registrado_por' => $admin->id]);
                Nota::create(['postulante_id' => $p->id, 'examen_id' => $exam3->id, 'puntaje' => $n3, 'registrado_por' => $admin->id]);
            }

            if (($idx + 1) % 200 === 0) {
                $this->command->line("  → Asignadas notas a " . ($idx + 1) . " postulantes...");
            }
        }

        // 10. Procesar Rankings y Selección Final de Admisión
        // Esto distribuirá a los aprobados en: admitidos primera opción, admitidos segunda opción y no admitidos.
        $this->command->info('Procesando rankings de admisión e ingresos...');
        $admissionService = new AdmissionSelectionService();
        $admissionResult = $admissionService->processAdmissions($gestion->id);

        $this->command->info('✓ Resultados del proceso de admisión:');
        $this->command->info('  - Aprobados Totales: ' . $admissionResult['aprobados_totales']);
        $this->command->info('  - Reprobados Académicos: ' . $admissionResult['reprobados_academicos']);

        $conteoAdmitidos1ra = Postulante::where('gestion_id', $gestion->id)->where('estado_admision', 'admitido_primera_opcion')->count();
        $conteoAdmitidos2da = Postulante::where('gestion_id', $gestion->id)->where('estado_admision', 'admitido_segunda_opcion')->count();
        $conteoNoAdmitidos = Postulante::where('gestion_id', $gestion->id)->where('estado_admision', 'no_admitido')->count();
        $conteoReprobados = Postulante::where('gestion_id', $gestion->id)->where('estado_admision', 'reprobado')->count();

        $this->command->info('  - Admitidos 1ra Opción: ' . $conteoAdmitidos1ra);
        $this->command->info('  - Admitidos 2da Opción: ' . $conteoAdmitidos2da);
        $this->command->info('  - No Admitidos (sin cupo): ' . $conteoNoAdmitidos);
        $this->command->info('  - Reprobados: ' . $conteoReprobados);

        $this->command->info('✅ ¡Población de datos completada exitosamente!');
    }
}
