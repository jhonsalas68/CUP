<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Permisos
        $permissions = [
            'acceder-admin',
            'acceder-coordinador',
            'acceder-docente',
            'acceder-postulante',
            'gestionar-usuarios',
            'calificar-examenes',
            'postularse',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Roles
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrador']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleCoordinador = Role::firstOrCreate(['name' => 'Coordinador']);
        $roleCoordinador->givePermissionTo(['acceder-coordinador', 'calificar-examenes']);

        $roleDocente = Role::firstOrCreate(['name' => 'Docente']);
        $roleDocente->givePermissionTo(['acceder-docente', 'calificar-examenes']);

        $rolePostulante = Role::firstOrCreate(['name' => 'Postulante']);
        $rolePostulante->givePermissionTo(['acceder-postulante', 'postularse']);

        // 4. Crear o Actualizar Administrador General
        $admin = User::updateOrCreate(
            ['email' => 'admin@cup.edu.bo'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['Administrador']);

        // 5. Crear Coordinador
        $coordinador = User::updateOrCreate(
            ['email' => 'coordinador@cup.edu.bo'],
            [
                'name' => 'Coordinador de Admisión',
                'password' => Hash::make('password'),
            ]
        );
        $coordinador->syncRoles(['Coordinador']);

        // 6. Crear Docente de Prueba
        $docente = User::updateOrCreate(
            ['email' => 'docente1@cup.edu.bo'],
            [
                'name' => 'Juan Pérez (Docente)',
                'password' => Hash::make('password'),
            ]
        );
        $docente->syncRoles(['Docente']);

        // 7. Crear Postulante de Prueba
        $postulante = User::updateOrCreate(
            ['email' => 'postulante1@cup.edu.bo'],
            [
                'name' => 'Ana Gómez (Postulante)',
                'password' => Hash::make('password'),
            ]
        );
        $postulante->syncRoles(['Postulante']);

        $this->command->info('✅ ¡Usuarios y Roles creados exitosamente en 0.1 segundos!');
    }
}
