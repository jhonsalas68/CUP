<?php

namespace App\Livewire\Shared;

use App\Models\Carrera;
use App\Models\Grupo;
use App\Models\Postulante;
use Livewire\Component;

class CommandPalette extends Component
{
    public $isOpen = false;

    public $search = '';

    protected $listeners = [
        'openCommandPalette' => 'open',
        'closeCommandPalette' => 'close',
    ];

    public function open()
    {
        $this->isOpen = true;
        $this->search = '';
    }

    public function close()
    {
        $this->isOpen = false;
        $this->search = '';
    }

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
        if ($this->isOpen) {
            $this->search = '';
        }
    }

    public function render()
    {
        $navigationItems = [
            [
                'title' => 'Dashboard Principal',
                'description' => 'Ver resúmenes, estadísticas y KPIs generales',
                'url' => route('dashboard'),
                'icon' => 'squares-2x2',
                'category' => 'Navegación',
            ],
            [
                'title' => 'Calculadora de Admisión',
                'description' => 'Simulador interactivo de notas y proyecciones',
                'url' => route('calculadora'),
                'icon' => 'calculator',
                'category' => 'Navegación',
            ],
        ];

        $user = auth()->user();
        if ($user && $user->hasAnyRole(['Administrador', 'Coordinador'])) {
            $navigationItems[] = [
                'title' => 'Gestión de Carreras y Cupos',
                'description' => 'Administrar carreras y cupos de 1ª y 2ª opción',
                'url' => route('admin.carreras'),
                'icon' => 'academic-cap',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Gestión de Materias',
                'description' => 'Administrar plan de estudio preuniversitario',
                'url' => route('admin.materias'),
                'icon' => 'book-open',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Gestión de Grupos Académicos',
                'description' => 'Ver y conformar grupos de postulantes',
                'url' => route('admin.grupos'),
                'icon' => 'rectangle-stack',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Gestión de Docentes',
                'description' => 'Asignar docentes a materias y grupos',
                'url' => route('admin.docentes'),
                'icon' => 'users',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Gestión de Postulantes',
                'description' => 'Buscar, ver notas y modificar postulantes',
                'url' => route('admin.postulantes'),
                'icon' => 'identification',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Carga Masiva de Postulantes',
                'description' => 'Importar postulantes desde archivo CSV',
                'url' => route('admin.carga-lotes'),
                'icon' => 'document-arrow-up',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Gestión de Exámenes',
                'description' => 'Crear exámenes y configurar ponderaciones',
                'url' => route('admin.examenes'),
                'icon' => 'clipboard-document-check',
                'category' => 'Navegación',
            ];
            $navigationItems[] = [
                'title' => 'Distribución de Aulas',
                'description' => 'Administrar aulas y capacidad física',
                'url' => route('admin.aulas'),
                'icon' => 'home',
                'category' => 'Navegación',
            ];
            if ($user->hasRole('Administrador')) {
                $navigationItems[] = [
                    'title' => 'Bitácora y Logs de Auditoría',
                    'description' => 'Ver historial de cambios y acciones del sistema',
                    'url' => route('admin.bitacora'),
                    'icon' => 'command-line',
                    'category' => 'Navegación',
                ];
            }
        }

        $query = trim($this->search);
        $results = [
            'navigation' => [],
            'postulantes' => [],
            'carreras' => [],
            'grupos' => [],
        ];

        // Filter navigation items
        if ($query === '') {
            $results['navigation'] = array_slice($navigationItems, 0, 5);
        } else {
            $results['navigation'] = array_values(array_filter($navigationItems, function ($item) use ($query) {
                return stripos($item['title'], $query) !== false || stripos($item['description'], $query) !== false;
            }));

            // Search Postulantes
            $results['postulantes'] = Postulante::with('carreraPrimeraOpn')
                ->where('nombres_apellidos', 'like', "%{$query}%")
                ->orWhere('ci', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            // Search Carreras
            $results['carreras'] = Carrera::where('nombre', 'like', "%{$query}%")
                ->orWhere('sigla', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            // Search Grupos
            $results['grupos'] = Grupo::with('materia')
                ->where('nombre', 'like', "%{$query}%")
                ->limit(5)
                ->get();
        }

        return view('livewire.shared.command-palette', [
            'results' => $results,
        ]);
    }
}
