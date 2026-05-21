<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
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

        // Create roles and assign existing permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrador']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleCoordinador = Role::firstOrCreate(['name' => 'Coordinador']);
        $roleCoordinador->givePermissionTo([
            'acceder-coordinador',
            'calificar-examenes',
        ]);

        $roleDocente = Role::firstOrCreate(['name' => 'Docente']);
        $roleDocente->givePermissionTo([
            'acceder-docente',
            'calificar-examenes',
        ]);

        $rolePostulante = Role::firstOrCreate(['name' => 'Postulante']);
        $rolePostulante->givePermissionTo([
            'acceder-postulante',
            'postularse',
        ]);
    }
}
