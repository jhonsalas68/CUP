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
        $carreraSIS = Carrera::where('sigla', 'SIS')->first();
        $carreraINF = Carrera::where('sigla', 'INF')->first();
        $carreraRED = Carrera::where('sigla', 'RED')->first();
        $carreraROB = Carrera::where('sigla', 'ROB')->first();

        $materias = [
            // Sistemas
            [
                'nombre' => 'Matemáticas (Sistemas)',
                'sigla' => 'MAT-SIS',
                'carrera_id' => $carreraSIS->id,
            ],
            [
                'nombre' => 'Física (Sistemas)',
                'sigla' => 'FIS-SIS',
                'carrera_id' => $carreraSIS->id,
            ],
            [
                'nombre' => 'Inglés (Sistemas)',
                'sigla' => 'ING-SIS',
                'carrera_id' => $carreraSIS->id,
            ],
            [
                'nombre' => 'Computación (Sistemas)',
                'sigla' => 'COM-SIS',
                'carrera_id' => $carreraSIS->id,
            ],

            // Informática
            [
                'nombre' => 'Matemáticas (Informática)',
                'sigla' => 'MAT-INF',
                'carrera_id' => $carreraINF->id,
            ],
            [
                'nombre' => 'Física (Informática)',
                'sigla' => 'FIS-INF',
                'carrera_id' => $carreraINF->id,
            ],
            [
                'nombre' => 'Inglés (Informática)',
                'sigla' => 'ING-INF',
                'carrera_id' => $carreraINF->id,
            ],
            [
                'nombre' => 'Computación (Informática)',
                'sigla' => 'COM-INF',
                'carrera_id' => $carreraINF->id,
            ],

            // Redes y Telecomunicaciones
            [
                'nombre' => 'Matemáticas (Redes)',
                'sigla' => 'MAT-RED',
                'carrera_id' => $carreraRED->id,
            ],
            [
                'nombre' => 'Física (Redes)',
                'sigla' => 'FIS-RED',
                'carrera_id' => $carreraRED->id,
            ],
            [
                'nombre' => 'Inglés (Redes)',
                'sigla' => 'ING-RED',
                'carrera_id' => $carreraRED->id,
            ],
            [
                'nombre' => 'Computación (Redes)',
                'sigla' => 'COM-RED',
                'carrera_id' => $carreraRED->id,
            ],

            // Robótica
            [
                'nombre' => 'Matemáticas (Robótica)',
                'sigla' => 'MAT-ROB',
                'carrera_id' => $carreraROB->id,
            ],
            [
                'nombre' => 'Física (Robótica)',
                'sigla' => 'FIS-ROB',
                'carrera_id' => $carreraROB->id,
            ],
            [
                'nombre' => 'Inglés (Robótica)',
                'sigla' => 'ING-ROB',
                'carrera_id' => $carreraROB->id,
            ],
            [
                'nombre' => 'Computación (Robótica)',
                'sigla' => 'COM-ROB',
                'carrera_id' => $carreraROB->id,
            ],
        ];

        foreach ($materias as $materia) {
            Materia::firstOrCreate(['sigla' => $materia['sigla']], $materia);
        }
    }
}
