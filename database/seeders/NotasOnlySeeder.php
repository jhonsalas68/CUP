<?php

namespace Database\Seeders;

use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Nota;
use App\Models\Postulante;
use App\Services\ExamService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotasOnlySeeder extends Seeder
{
    public function run(): void
    {
        $gestion = Gestion::where('activo', true)->first() ?? Gestion::latest()->first();

        if (!$gestion) {
            $this->command->error('No existe ninguna gestión académica registrada.');
            return;
        }

        $this->command->info("Generando y asignando notas para la gestión {$gestion->nombre}...");
        $examService = new ExamService();

        DB::transaction(function () use ($gestion, $examService) {
            $postulantes = Postulante::with('carreraPrimeraOpn.materias')->get();
            $examenesGrouped = Examen::where('gestion_id', $gestion->id)->get()->groupBy('materia_id');

            $countNotas = 0;

            foreach ($postulantes as $postulante) {
                $materiasIds = $postulante->carreraPrimeraOpn?->materias?->pluck('id') ?? collect();

                foreach ($materiasIds as $materiaId) {
                    $materiaExams = $examenesGrouped->get($materiaId) ?? collect();
                    foreach ($materiaExams as $exam) {
                        Nota::firstOrCreate(
                            [
                                'postulante_id' => $postulante->id,
                                'examen_id' => $exam->id,
                            ],
                            [
                                'puntaje' => rand(45, 95),
                            ]
                        );
                        $countNotas++;
                    }
                }
                // Recalcular la nota final y estado del postulante
                $examService->recalculatePostulanteScore($postulante->id, $gestion->id);
            }

            $this->command->info("✅ Se han asignado y recalculado notas para {$postulantes->count()} postulantes ({$countNotas} notas procesadas).");
        });
    }
}
