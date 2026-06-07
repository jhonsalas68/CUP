<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Docente;
use App\Models\Gestion;
use App\Models\Postulante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Correr seeders de Roles y Permisos, Carreras y Materias
        $this->call([
            RolesAndPermissionsSeeder::class,
            CarreraSeeder::class,
            MateriaSeeder::class,
        ]);

        // 2. Crear Gestión Activa de Prueba (I-2026)
        $gestion = Gestion::firstOrCreate(
            ['nombre' => 'I-2026'],
            [
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-06-30',
                'activo' => true,
            ]
        );

        // 3. Configurar Cupos para las Carreras en la Gestión I-2026
        $carreras = Carrera::all();
        foreach ($carreras as $carrera) {
            Cupo::firstOrCreate(
                [
                    'carrera_id' => $carrera->id,
                    'gestion_id' => $gestion->id,
                ],
                [
                    'cantidad_primera_opcion' => 10,
                    'cantidad_segunda_opcion' => 5,
                ]
            );
        }

        // 4. Crear Usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@cup.edu.bo'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('Administrador');

        // 5. Crear Usuario Coordinador
        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@cup.edu.bo'],
            [
                'name' => 'Coordinador de Admisión',
                'password' => Hash::make('password'),
            ]
        );
        $coordinador->assignRole('Coordinador');

        // 6. Crear Docente de Prueba
        $userDocente = User::firstOrCreate(
            ['email' => 'docente1@cup.edu.bo'],
            [
                'name' => 'Juan Pérez (Docente)',
                'password' => Hash::make('password'),
            ]
        );
        $userDocente->assignRole('Docente');

        Docente::firstOrCreate(
            ['user_id' => $userDocente->id],
            [
                'ci' => '9876543',
                'telefono' => '71122334',
                'especialidad' => 'Informática y Programación',
            ]
        );

        // 7. Crear Postulante de Prueba
        $userPostulante = User::firstOrCreate(
            ['email' => 'postulante1@cup.edu.bo'],
            [
                'name' => 'Ana Gómez (Postulante)',
                'password' => Hash::make('password'),
            ]
        );
        $userPostulante->assignRole('Postulante');

        $carreraSistemas = Carrera::where('sigla', 'SIS')->first();
        $carreraInformatica = Carrera::where('sigla', 'INF')->first();

        Postulante::firstOrCreate(
            ['user_id' => $userPostulante->id],
            [
                'ci' => '1234567',
                'telefono' => '60011223',
                'fecha_nacimiento' => '2005-08-15',
                'carrera_primera_opcion_id' => $carreraSistemas->id,
                'carrera_segunda_opcion_id' => $carreraInformatica->id,
                'gestion_id' => $gestion->id,
                'estado_admision' => 'pendiente',
                'nota_final' => null,
                'ci_vigente' => true,
                'titulo_bachiller' => true,
                'libreta_legalizada' => true,
            ]
        );
    }
}
