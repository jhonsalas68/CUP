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

class ComprehensiveDataSeeder extends Seeder
{
    private const POSTULANTES_COUNT = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener o crear gestión activa
        $gestion = Gestion::where('activo', true)->first();
        if (!$gestion) {
            $gestion = Gestion::create([
                'nombre' => 'I-2026',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-06-30',
                'activo' => true,
            ]);
        }

        // 2. Verificar que existan las 4 carreras principales
        $carreras = Carrera::all();
        if ($carreras->count() < 4) {
            $this->call(CarreraSeeder::class);
            $carreras = Carrera::all();
        }

        // 3. Verificar que existan materias
        $materias = Materia::all();
        if ($materias->isEmpty()) {
            $this->call(MateriaSeeder::class);
            $materias = Materia::all();
        }

        // 4. Configurar cupos por carrera para la gestión activa
        $this->command->info('Configurando cupos...');
        foreach ($carreras as $carrera) {
            Cupo::firstOrCreate(
                [
                    'carrera_id' => $carrera->id,
                    'gestion_id' => $gestion->id,
                ],
                [
                    'cantidad_primera_opcion' => 80,
                    'cantidad_segunda_opcion' => 45,
                ]
            );
        }

        // 5. Crear usuarios docentes calificados
        $this->command->info('Creando docentes calificados...');

        $hasFaker = class_exists(\Faker\Factory::class);
        $faker = $hasFaker ? \Faker\Factory::create('es_ES') : null;

        $firstNames = ['Carlos', 'Juan', 'María', 'Ana', 'Pedro', 'Luis', 'Sofía', 'Lucía', 'Diego', 'Mateo', 'Gabriel', 'Valentina', 'Camila', 'Santiago', 'Daniel'];
        $lastNames = ['Pérez', 'Gómez', 'Rojas', 'López', 'Flores', 'Vargas', 'Quispe', 'Mamani', 'Ramos', 'Gutiérrez', 'Castillo', 'Sanchez', 'Ortiz', 'Aguilar'];

        $getRandomName = function () use ($hasFaker, $faker, $firstNames, $lastNames) {
            if ($hasFaker) {
                return $faker->firstName() . ' ' . $faker->lastName() . ' ' . $faker->lastName();
            }
            return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)];
        };

        $getNumerify = function ($pattern) use ($hasFaker, $faker) {
            if ($hasFaker) {
                return $faker->numerify($pattern);
            }
            return preg_replace_callback('/#/', fn() => rand(1, 9), $pattern);
        };

        $getRandomElement = function ($arr) use ($hasFaker, $faker) {
            if ($hasFaker) {
                return $faker->randomElement($arr);
            }
            return $arr[array_rand($arr)];
        };

        $passwordHash = Hash::make('password');
        $docentes = collect();

        for ($i = 1; $i <= 20; $i++) {
            $nombre = $getRandomName();
            $email = 'docente' . $i . '@cup.edu.bo';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nombre,
                    'password' => $passwordHash,
                ]
            );
            $user->assignRole('Docente');

            $docente = Docente::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre' => $nombre,
                    'ci' => $getNumerify('########'),
                    'telefono' => $getNumerify('7#######'),
                    'especialidad' => $getRandomElement([
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
                ]
            );

            // Asociar el docente a todas las materias para maximizar compatibilidad
            $docente->materias()->syncWithoutDetaching($materias->pluck('id'));
            $docentes->push($docente);
        }

        // 6. Crear 1000 postulantes de prueba
        $this->command->info('Creando 1000 postulantes...');

        for ($i = 1; $i <= self::POSTULANTES_COUNT; $i++) {
            $carreraPrimera = $carreras[$i % $carreras->count()];
            $carrerasFiltradas = $carreras->filter(fn($c) => $c->id !== $carreraPrimera->id)->values();
            $carreraSegunda = $carrerasFiltradas[$i % $carrerasFiltradas->count()];

            $nombre = $getRandomName();
            $email = 'postulante' . $i . '@cup.edu.bo';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nombre,
                    'password' => $passwordHash,
                ]
            );
            $user->assignRole('Postulante');

            Postulante::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres_apellidos' => $nombre,
                    'ci' => (1000000 + $i),
                    'telefono' => $getNumerify('6#######'),
                    'fecha_nacimiento' => '2004-05-15',
                    'sexo' => $getRandomElement(['M', 'F']),
                    'direccion' => 'Av. Busch # ' . $i,
                    'colegio_procedencia' => $getRandomElement([
                        'Colegio Nacional Florida',
                        'Colegio La Salle',
                        'U.E. Marista',
                        'U.E. Fe y Alegría',
                        'U.E. Don Bosco'
                    ]),
                    'ciudad' => $getRandomElement(['Santa Cruz', 'La Paz', 'Cochabamba', 'Oruro', 'Sucre']),
                    'carrera_primera_opcion_id' => $carreraPrimera->id,
                    'carrera_segunda_opcion_id' => $carreraSegunda->id,
                    'gestion_id' => $gestion->id,
                    'estado_admision' => 'pendiente',
                    'ci_vigente' => true,
                    'titulo_bachiller' => true,
                    'libreta_legalizada' => true,
                    'habilitado' => true,
                    'pago_realizado' => true,
                    'pago_matricula_realizado' => false,
                ]
            );
        }

        // 7. Ejecutar generación automática de grupos
        $this->command->info('Generando grupos automáticos...');
        $groupService = new GroupGenerationService();
        $groupService->generateGroupsForGestion($gestion->id);

        // 8. Crear exámenes para cada materia y gestión
        $this->command->info('Creando exámenes...');
        foreach ($materias as $materia) {
            Examen::firstOrCreate(
                [
                    'materia_id' => $materia->id,
                    'gestion_id' => $gestion->id,
                    'nombre' => 'Primer Parcial',
                ],
                [
                    'ponderacion' => 30.00,
                    'fecha' => now()->subDays(30)->format('Y-m-d'),
                ]
            );

            Examen::firstOrCreate(
                [
                    'materia_id' => $materia->id,
                    'gestion_id' => $gestion->id,
                    'nombre' => 'Segundo Parcial',
                ],
                [
                    'ponderacion' => 30.00,
                    'fecha' => now()->subDays(15)->format('Y-m-d'),
                ]
            );

            Examen::firstOrCreate(
                [
                    'materia_id' => $materia->id,
                    'gestion_id' => $gestion->id,
                    'nombre' => 'Examen Final',
                ],
                [
                    'ponderacion' => 40.00,
                    'fecha' => now()->subDays(2)->format('Y-m-d'),
                ]
            );
        }

        // 9. Asignar notas a los postulantes
        $this->command->info('Asignando notas...');
        $examService = new ExamService();
        $postulantes = Postulante::all();
        $examenes = Examen::where('gestion_id', $gestion->id)->get();

        foreach ($postulantes as $postulante) {
            foreach ($examenes as $exam) {
                Nota::firstOrCreate(
                    [
                        'postulante_id' => $postulante->id,
                        'examen_id' => $exam->id,
                    ],
                    [
                        'puntaje' => rand(40, 95),
                    ]
                );
            }
            // Recalcular nota final del postulante
            $examService->calculateFinalGrade($postulante->id, $gestion->id);
        }

        $this->command->info('✅ ¡Población de datos completada exitosamente con 1000 postulantes!');
    }
}
