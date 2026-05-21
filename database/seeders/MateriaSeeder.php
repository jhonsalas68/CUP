<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreraSistemas = Carrera::where('sigla', 'SIS')->first();
        $carreraCivil = Carrera::where('sigla', 'CIV')->first();
        $carreraMedicina = Carrera::where('sigla', 'MED')->first();

        $materias = [
            [
                'nombre' => 'Introducción a la Programación',
                'sigla' => 'INF-110',
                'carrera_id' => $carreraSistemas->id,
            ],
            [
                'nombre' => 'Cálculo I (Sistemas)',
                'sigla' => 'MAT-101-SIS',
                'carrera_id' => $carreraSistemas->id,
            ],
            [
                'nombre' => 'Cálculo I (Civil)',
                'sigla' => 'MAT-101-CIV',
                'carrera_id' => $carreraCivil->id,
            ],
            [
                'nombre' => 'Anatomía Humana',
                'sigla' => 'MED-101',
                'carrera_id' => $carreraMedicina->id,
            ],
        ];

        foreach ($materias as $materia) {
            Materia::firstOrCreate(['sigla' => $materia['sigla']], $materia);
        }
    }
}
