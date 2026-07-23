<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Postulante;
use App\Models\User;
use App\Services\ExamService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class CargaLotes extends Component
{
    use WithFileUploads;

    public $file;

    public $selectedGestionId = '';

    public $gestiones = [];

    public $errorsList = [];

    public $successCount = 0;

    public $isProcessed = false;

    public function mount()
    {
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $active = $this->gestiones->where('activo', true)->first() ?? $this->gestiones->first();
        $this->selectedGestionId = $active ? $active->id : '';
    }

    public function downloadTemplate()
    {
        $headers = [
            'nombre',
            'email',
            'ci',
            'telefono',
            'fecha_nacimiento',
            'sexo',
            'direccion',
            'colegio',
            'ciudad',
            'carrera_1ra',
            'carrera_2da',
            'ci_vigente',
            'titulo_bachiller',
            'libreta_legalizada',
        ];

        $sampleRow = [
            'Carlos Perez',
            'carlosperez@cup.edu.bo',
            '1234567',
            '78912345',
            '2005-08-15',
            'M',
            'Av. Busch #456',
            'Colegio Nacional Florida',
            'Santa Cruz',
            'SIS',
            'INF',
            '1',
            '1',
            '1',
        ];

        return response()->streamDownload(function () use ($headers, $sampleRow) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM to open correctly in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);
            fputcsv($handle, $sampleRow);
            fclose($handle);
        }, 'plantilla_carga_postulantes.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function procesar()
    {
        $this->reset(['errorsList', 'successCount', 'isProcessed']);

        // 1. File Upload Validation
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
            'selectedGestionId' => 'required|exists:gestiones,id',
        ], [
            'file.required' => 'Debe seleccionar un archivo CSV para subir.',
            'file.mimes' => 'El archivo debe ser de formato CSV.',
            'file.max' => 'El tamaño máximo del archivo es de 4MB.',
            'selectedGestionId.required' => 'Debe seleccionar la gestión académica de destino.',
        ]);

        $filePath = $this->file->getRealPath();

        // Open file
        if (($handle = fopen($filePath, 'r')) === false) {
            $this->addError('file', 'No se pudo abrir el archivo subido.');

            return;
        }

        // 2. Read headers
        $firstRow = fgetcsv($handle, 10000, ',');
        if (! $firstRow) {
            $this->addError('file', 'El archivo CSV está vacío.');
            fclose($handle);

            return;
        }

        // Clean headers: lowercase and remove accents/BOM
        $headers = array_map(function ($h) {
            $h = preg_replace('/[\x{FEFF}\x{200B}]/u', '', $h); // remove BOM

            return trim(mb_strtolower($h, 'UTF-8'));
        }, $firstRow);

        // Required headers mapping
        $headerMap = [];
        $requiredHeaders = [
            'nombre' => ['nombre', 'name', 'nombre completo'],
            'email' => ['email', 'correo', 'correo electrónico', 'email_address'],
            'ci' => ['ci', 'documento', 'cédula', 'cedula'],
            'telefono' => ['telefono', 'teléfono', 'phone', 'celular'],
            'fecha_nacimiento' => ['fecha_nacimiento', 'nacimiento', 'fecha de nacimiento'],
            'sexo' => ['sexo', 'genero', 'género'],
            'direccion' => ['direccion', 'dirección', 'domicilio'],
            'colegio' => ['colegio', 'colegio_procedencia', 'colegio de procedencia'],
            'ciudad' => ['ciudad', 'provincia', 'departamento'],
            'carrera_1ra' => ['carrera_1ra', 'carrera_primera', 'carrera 1ra', 'carrera primera', 'primera opcion'],
            'carrera_2da' => ['carrera_2da', 'carrera_segunda', 'carrera 2da', 'carrera segunda', 'segunda opcion'],
            'ci_vigente' => ['ci_vigente', 'ci vigente', 'carnet vigente'],
            'titulo_bachiller' => ['titulo_bachiller', 'titulo bachiller', 'bachiller'],
            'libreta_legalizada' => ['libreta_legalizada', 'libreta legalizada', 'libreta'],
        ];

        // Match CSV headers with our map
        foreach ($requiredHeaders as $key => $aliases) {
            $foundIndex = -1;
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $headers);
                if ($idx !== false) {
                    $foundIndex = $idx;
                    break;
                }
            }
            $headerMap[$key] = $foundIndex;
        }

        // Enforce presence of critical columns
        $missing = [];
        $criticalKeys = ['nombre', 'email', 'ci', 'telefono', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio', 'ciudad', 'carrera_1ra'];
        foreach ($criticalKeys as $crit) {
            if ($headerMap[$crit] === -1) {
                $missing[] = $crit;
            }
        }

        if (! empty($missing)) {
            $this->addError('file', 'Faltan columnas obligatorias en la cabecera del CSV: '.implode(', ', $missing));
            fclose($handle);

            return;
        }

        // 3. Cache Database Queries in Memory (Optimized, no N+1)
        $carrerasMap = Carrera::pluck('id', 'sigla')
            ->mapWithKeys(fn ($id, $sigla) => [strtoupper(trim($sigla)) => $id])
            ->toArray();

        $existingEmails = User::pluck('email')
            ->map(fn ($e) => mb_strtolower(trim($e), 'UTF-8'))
            ->toArray();

        $existingCis = Postulante::pluck('ci')
            ->map(fn ($c) => trim($c))
            ->toArray();

        $processedEmails = [];
        $processedCis = [];
        $rowsToInsert = [];
        $lineNumber = 1; // header is 1

        // 4. Validate rows in memory first
        while (($row = fgetcsv($handle, 10000, ',')) !== false) {
            $lineNumber++;

            // Skip empty rows
            if (empty($row) || count($row) < 5 || (count($row) === 1 && empty(trim($row[0])))) {
                continue;
            }

            // Extract values using map
            $nombre = trim($row[$headerMap['nombre']] ?? '');
            $email = mb_strtolower(trim($row[$headerMap['email']] ?? ''), 'UTF-8');
            $ci = trim($row[$headerMap['ci']] ?? '');
            $telefono = trim($row[$headerMap['telefono']] ?? '');
            $fecha_nacimiento = trim($row[$headerMap['fecha_nacimiento']] ?? '');
            $sexo = strtoupper(trim($row[$headerMap['sexo']] ?? ''));
            $direccion = trim($row[$headerMap['direccion']] ?? '');
            $colegio = trim($row[$headerMap['colegio']] ?? '');
            $ciudad = trim($row[$headerMap['ciudad']] ?? '');
            $carrera_1ra_sigla = strtoupper(trim($row[$headerMap['carrera_1ra']] ?? ''));

            $carrera_2da_sigla = '';
            if ($headerMap['carrera_2da'] !== -1) {
                $carrera_2da_sigla = strtoupper(trim($row[$headerMap['carrera_2da']] ?? ''));
            }

            $ci_vigente = false;
            if ($headerMap['ci_vigente'] !== -1) {
                $ci_vigente = filter_var($row[$headerMap['ci_vigente']] ?? false, FILTER_VALIDATE_BOOLEAN) || trim($row[$headerMap['ci_vigente']] ?? '0') === '1';
            }

            $titulo_bachiller = false;
            if ($headerMap['titulo_bachiller'] !== -1) {
                $titulo_bachiller = filter_var($row[$headerMap['titulo_bachiller']] ?? false, FILTER_VALIDATE_BOOLEAN) || trim($row[$headerMap['titulo_bachiller']] ?? '0') === '1';
            }

            $libreta_legalizada = false;
            if ($headerMap['libreta_legalizada'] !== -1) {
                $libreta_legalizada = filter_var($row[$headerMap['libreta_legalizada']] ?? false, FILTER_VALIDATE_BOOLEAN) || trim($row[$headerMap['libreta_legalizada']] ?? '0') === '1';
            }

            $rowErrors = [];

            // Fields validation
            if (empty($nombre)) {
                $rowErrors[] = 'El nombre es obligatorio.';
            }
            if (empty($email)) {
                $rowErrors[] = 'El correo electrónico es obligatorio.';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "El correo '{$email}' no tiene un formato válido.";
            }
            if (empty($ci)) {
                $rowErrors[] = 'El CI es obligatorio.';
            }
            if (empty($telefono)) {
                $rowErrors[] = 'El teléfono es obligatorio.';
            }
            if (empty($direccion)) {
                $rowErrors[] = 'La dirección es obligatoria.';
            }
            if (empty($colegio)) {
                $rowErrors[] = 'El colegio de procedencia es obligatorio.';
            }
            if (empty($ciudad)) {
                $rowErrors[] = 'La ciudad es obligatoria.';
            }

            // Sexo validation
            if (! in_array($sexo, ['M', 'F'])) {
                $rowErrors[] = "El campo sexo debe ser 'M' o 'F' (se recibió '{$sexo}').";
            }

            // Birthdate validation
            if (empty($fecha_nacimiento)) {
                $rowErrors[] = 'La fecha de nacimiento es obligatoria.';
            } else {
                $time = strtotime($fecha_nacimiento);
                if (! $time) {
                    $rowErrors[] = "La fecha de nacimiento '{$fecha_nacimiento}' no es válida. Use formato AAAA-MM-DD.";
                } else {
                    $fecha_nacimiento = date('Y-m-d', $time);
                }
            }

            // Careers validation
            if (empty($carrera_1ra_sigla)) {
                $rowErrors[] = 'La carrera de 1ra opción es obligatoria.';
            } elseif (! isset($carrerasMap[$carrera_1ra_sigla])) {
                $rowErrors[] = "La carrera de 1ra opción con sigla '{$carrera_1ra_sigla}' no existe.";
            }

            if (! empty($carrera_2da_sigla)) {
                if (! isset($carrerasMap[$carrera_2da_sigla])) {
                    $rowErrors[] = "La carrera de 2da opción con sigla '{$carrera_2da_sigla}' no existe.";
                } elseif ($carrera_1ra_sigla === $carrera_2da_sigla) {
                    $rowErrors[] = 'La carrera de 2da opción debe ser diferente a la de 1ra opción.';
                }
            }

            // Duplicates validation against Database
            if (in_array($email, $existingEmails)) {
                $rowErrors[] = "El correo electrónico '{$email}' ya está registrado en la base de datos.";
            }
            if (in_array($ci, $existingCis)) {
                $rowErrors[] = "El CI '{$ci}' ya está registrado en la base de datos.";
            }

            // Duplicates validation inside the CSV
            if (in_array($email, $processedEmails)) {
                $rowErrors[] = "El correo electrónico '{$email}' está repetido dentro del mismo archivo CSV.";
            } else {
                $processedEmails[] = $email;
            }

            if (in_array($ci, $processedCis)) {
                $rowErrors[] = "El CI '{$ci}' está repetido dentro del mismo archivo CSV.";
            } else {
                $processedCis[] = $ci;
            }

            // If there are errors in this line, record them and keep checking others
            if (! empty($rowErrors)) {
                $this->errorsList[] = "Línea {$lineNumber}: ".implode(' ', $rowErrors);
            } else {
                $rowsToInsert[] = [
                    'name' => $nombre,
                    'email' => $email,
                    'ci' => $ci,
                    'telefono' => $telefono,
                    'fecha_nacimiento' => $fecha_nacimiento,
                    'sexo' => $sexo,
                    'direccion' => $direccion,
                    'colegio' => $colegio,
                    'ciudad' => $ciudad,
                    'carrera_primera_opcion_id' => $carrerasMap[$carrera_1ra_sigla],
                    'carrera_segunda_opcion_id' => ! empty($carrera_2da_sigla) ? $carrerasMap[$carrera_2da_sigla] : null,
                    'ci_vigente' => $ci_vigente,
                    'titulo_bachiller' => $titulo_bachiller,
                    'libreta_legalizada' => $libreta_legalizada,
                ];
            }
        }

        fclose($handle);

        // 5. Transactional Execution
        if (! empty($this->errorsList)) {
            // Cancel whole process if any row fails
            $this->isProcessed = true;

            return;
        }

        if (empty($rowsToInsert)) {
            $this->addError('file', 'No se encontraron registros válidos para importar.');

            return;
        }

        DB::beginTransaction();
        try {
            $examService = new ExamService;

            foreach ($rowsToInsert as $data) {
                // Create User
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('Postulante');

                // Create Postulante
                $postulante = Postulante::create([
                    'user_id' => $user->id,
                    'nombres_apellidos' => $data['name'],
                    'ci' => $data['ci'],
                    'telefono' => $data['telefono'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'sexo' => $data['sexo'],
                    'direccion' => $data['direccion'],
                    'colegio_procedencia' => $data['colegio'],
                    'ciudad' => $data['ciudad'],
                    'carrera_primera_opcion_id' => $data['carrera_primera_opcion_id'],
                    'carrera_segunda_opcion_id' => $data['carrera_segunda_opcion_id'],
                    'gestion_id' => $this->selectedGestionId,
                    'estado_admision' => 'pendiente',
                    'ci_vigente' => $data['ci_vigente'],
                    'titulo_bachiller' => $data['titulo_bachiller'],
                    'libreta_legalizada' => $data['libreta_legalizada'],
                ]);

                // Recalculate score
                $examService->recalculatePostulanteScore($postulante->id, $postulante->gestion_id);
                $this->successCount++;
            }

            DB::commit();
            session()->flash('message', "Se cargaron exitosamente {$this->successCount} postulantes.");
            $this->isProcessed = true;
            $this->reset('file');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorsList[] = 'Error interno durante la inserción en base de datos: '.$e->getMessage();
            $this->isProcessed = true;
        }
    }

    public function render()
    {
        return view('livewire.admin.carga-lotes')->layout('layouts.admin');
    }
}
