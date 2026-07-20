<?php

namespace Tests\Feature;

use App\Livewire\Shared\CommandPalette;
use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Postulante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_palette_renders_and_opens()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CommandPalette::class)
            ->assertSet('isOpen', false)
            ->call('open')
            ->assertSet('isOpen', true)
            ->call('close')
            ->assertSet('isOpen', false);
    }

    public function test_command_palette_searches_postulantes_and_carreras()
    {
        $user = User::factory()->create();

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activa' => true,
        ]);

        $carrera = Carrera::create([
            'nombre' => 'Ingeniería Informática',
            'sigla' => 'INF',
            'codigo' => 'INF-100',
            'cupos_primera_opcion' => 40,
            'cupos_segunda_opcion' => 10,
        ]);

        $postulante = Postulante::create([
            'user_id' => $user->id,
            'gestion_id' => $gestion->id,
            'nombres_apellidos' => 'Carlos Valderrama',
            'ci' => '99887766',
            'carrera_primera_opcion_id' => $carrera->id,
        ]);

        $this->actingAs($user);

        Livewire::test(CommandPalette::class)
            ->set('search', 'Carlos')
            ->assertSee('Carlos Valderrama')
            ->assertSee('99887766')
            ->set('search', 'Informática')
            ->assertSee('Ingeniería Informática');
    }
}
