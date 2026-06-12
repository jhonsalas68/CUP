<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Postulante;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class CargaLotesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Gestion $gestion;
    private Carrera $carreraSIS;
    private Carrera $carreraINF;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Administrador');

        $this->gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->carreraSIS = Carrera::create([
            'nombre' => 'Sistemas',
            'sigla' => 'SIS',
        ]);

        $this->carreraINF = Carrera::create([
            'nombre' => 'Informática',
            'sigla' => 'INF',
        ]);
    }

    public function test_carga_lotes_requires_authorization()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get(route('admin.carga-lotes'))
            ->assertStatus(403);
    }

    public function test_carga_lotes_is_accessible_by_admin()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.carga-lotes'))
            ->assertStatus(200);
    }

    public function test_invalid_file_extension_fails()
    {
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        Livewire::test(\App\Livewire\Admin\CargaLotes::class)
            ->set('file', $file)
            ->set('selectedGestionId', $this->gestion->id)
            ->call('procesar')
            ->assertHasErrors(['file']);
    }

    public function test_missing_required_headers_fails()
    {
        $this->actingAs($this->admin);

        // CSV without CI and Carrera
        $csvContent = "nombre,email,telefono,fecha_nacimiento,sexo\nCarlos Perez,carlos@test.com,700000,2005-01-01,M";
        $file = UploadedFile::fake()->createWithContent('postulantes.csv', $csvContent);

        Livewire::test(\App\Livewire\Admin\CargaLotes::class)
            ->set('file', $file)
            ->set('selectedGestionId', $this->gestion->id)
            ->call('procesar')
            ->assertHasErrors(['file'])
            ->assertSee('Faltan columnas obligatorias');
    }

    public function test_row_validation_errors_reports_line_numbers_and_rolls_back()
    {
        $this->actingAs($this->admin);

        // Line 2 has invalid email and invalid career. Line 3 has missing CI.
        $csvContent = "nombre,email,ci,telefono,fecha_nacimiento,sexo,direccion,colegio,ciudad,carrera_1ra,ci_vigente,titulo_bachiller,libreta_legalizada\n" .
                      "Juan Perez,invalid-email,12345,78912,2005-08-15,M,Calle 1,Florida,SC,XYZ,1,1,1\n" .
                      "Ana Gomez,ana@test.com,,78913,2006-01-20,F,Calle 2,Florida,SC,SIS,1,1,1";
        
        $file = UploadedFile::fake()->createWithContent('postulantes.csv', $csvContent);

        Livewire::test(\App\Livewire\Admin\CargaLotes::class)
            ->set('file', $file)
            ->set('selectedGestionId', $this->gestion->id)
            ->call('procesar')
            ->assertSet('isProcessed', true)
            ->assertNotSet('errorsList', [])
            // Verify errors contain line numbers and reasons
            ->assertSee('Línea 2: El correo')
            ->assertSee('no tiene un formato válido')
            ->assertSee('con sigla \'XYZ\' no existe')
            ->assertSee('Línea 3: El CI es obligatorio');

        // Confirm no users or postulants were registered (rollback check)
        $this->assertDatabaseMissing('users', ['email' => 'ana@test.com']);
        $this->assertDatabaseMissing('postulantes', ['telefono' => '78912']);
    }

    public function test_duplicate_checks_fail_and_rolls_back()
    {
        // Register an existing user/postulant first
        $existingUser = User::create(['name' => 'Existing', 'email' => 'existing@test.com', 'password' => 'password']);
        Postulante::create([
            'user_id' => $existingUser->id,
            'ci' => '12345',
            'telefono' => '111',
            'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $this->carreraSIS->id,
            'gestion_id' => $this->gestion->id,
            'estado_admision' => 'pendiente',
        ]);

        $this->actingAs($this->admin);

        // CSV with duplicates against database and duplicates inside the file itself
        $csvContent = "nombre,email,ci,telefono,fecha_nacimiento,sexo,direccion,colegio,ciudad,carrera_1ra,ci_vigente,titulo_bachiller,libreta_legalizada\n" .
                      "Dup DB Email,existing@test.com,99999,78912,2005-08-15,M,Calle 1,Florida,SC,SIS,1,1,1\n" .
                      "Dup DB CI,new@test.com,12345,78912,2005-08-15,M,Calle 1,Florida,SC,SIS,1,1,1\n" .
                      "Inner Dup,inner@test.com,88888,78912,2005-08-15,M,Calle 1,Florida,SC,SIS,1,1,1\n" .
                      "Inner Dup2,inner@test.com,88888,78912,2005-08-15,M,Calle 1,Florida,SC,SIS,1,1,1";
        
        $file = UploadedFile::fake()->createWithContent('postulantes.csv', $csvContent);

        Livewire::test(\App\Livewire\Admin\CargaLotes::class)
            ->set('file', $file)
            ->set('selectedGestionId', $this->gestion->id)
            ->call('procesar')
            ->assertSet('isProcessed', true)
            ->assertSee('ya está registrado en la base de datos')
            ->assertSee('está repetido dentro del mismo archivo CSV');

        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);
    }

    public function test_valid_csv_imports_successfully_under_transaction()
    {
        $this->actingAs($this->admin);

        $csvContent = "nombre,email,ci,telefono,fecha_nacimiento,sexo,direccion,colegio,ciudad,carrera_1ra,carrera_2da,ci_vigente,titulo_bachiller,libreta_legalizada\n" .
                      "Carlos Perez,carlosperez@cup.edu.bo,111222,78912345,2005-08-15,M,Av. Busch #456,Colegio Florida,Santa Cruz,SIS,INF,1,1,1\n" .
                      "Ana Gomez,anagomez@cup.edu.bo,333444,78912346,2006-01-20,F,Calle Florida #45,Colegio Adventista,Santa Cruz,INF,,1,0,1";

        $file = UploadedFile::fake()->createWithContent('postulantes.csv', $csvContent);

        Livewire::test(\App\Livewire\Admin\CargaLotes::class)
            ->set('file', $file)
            ->set('selectedGestionId', $this->gestion->id)
            ->call('procesar')
            ->assertSet('isProcessed', true)
            ->assertSet('successCount', 2)
            ->assertSet('errorsList', []);

        // Verify Carlos Perez
        $userCarlos = User::where('email', 'carlosperez@cup.edu.bo')->first();
        $this->assertNotNull($userCarlos);
        $this->assertTrue($userCarlos->hasRole('Postulante'));

        $postCarlos = Postulante::where('user_id', $userCarlos->id)->first();
        $this->assertNotNull($postCarlos);
        $this->assertEquals('111222', $postCarlos->ci);
        $this->assertEquals($this->carreraSIS->id, $postCarlos->carrera_primera_opcion_id);
        $this->assertEquals($this->carreraINF->id, $postCarlos->carrera_segunda_opcion_id);
        $this->assertTrue((bool)$postCarlos->ci_vigente);
        $this->assertTrue((bool)$postCarlos->titulo_bachiller);
        $this->assertTrue((bool)$postCarlos->libreta_legalizada);

        // Verify Ana Gomez
        $userAna = User::where('email', 'anagomez@cup.edu.bo')->first();
        $this->assertNotNull($userAna);

        $postAna = Postulante::where('user_id', $userAna->id)->first();
        $this->assertNotNull($postAna);
        $this->assertEquals('333444', $postAna->ci);
        $this->assertEquals($this->carreraINF->id, $postAna->carrera_primera_opcion_id);
        $this->assertNull($postAna->carrera_segunda_opcion_id);
        $this->assertTrue((bool)$postAna->ci_vigente);
        $this->assertFalse((bool)$postAna->titulo_bachiller);
    }
}
