<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreras = [
            [
                'nombre' => 'Ingeniería de Sistemas',
                'sigla' => 'SIS',
            ],
            [
                'nombre' => 'Ingeniería Civil',
                'sigla' => 'CIV',
            ],
            [
                'nombre' => 'Medicina',
                'sigla' => 'MED',
            ],
        ];

        foreach ($carreras as $carrera) {
            Carrera::firstOrCreate(['sigla' => $carrera['sigla']], $carrera);
        }
    }
}
