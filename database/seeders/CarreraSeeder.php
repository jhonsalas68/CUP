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
                'nombre' => 'Ingeniería Informática',
                'sigla' => 'INF',
            ],
            [
                'nombre' => 'Ingeniería en Redes y Telecomunicaciones',
                'sigla' => 'RED',
            ],
            [
                'nombre' => 'Ingeniería Robótica',
                'sigla' => 'ROB',
            ],
        ];

        foreach ($carreras as $carrera) {
            Carrera::firstOrCreate(['sigla' => $carrera['sigla']], $carrera);
        }
    }
}
