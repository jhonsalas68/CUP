# Arquitectura y Organización de Componentes Livewire
## Sistema de Admisión Universitaria (CUP)

**Rol:** Arquitecto de Frontend / Livewire Expert  
**Tecnologías:** Livewire v3 + Laravel Flux + TailwindCSS  

Para asegurar la escalabilidad del sistema y evitar el antipatrón de **componentes gigantes (God Components)**, estructuraremos la capa de frontend utilizando las mejores prácticas de Livewire v3, incluyendo **Form Objects**, comunicación por **Eventos Reactivos** y la integración de **Laravel Flux**.

---

### 1. Estructura de Carpetas Recomendada (`app/Livewire`)

Organizaremos los componentes de acuerdo con el dominio del negocio (módulos) y separaremos los componentes de página completa de los subcomponentes reutilizables.

```
app/Livewire/
│
├── Forms/                         # Form Objects (Abstracción de validación y estado de formularios)
│   ├── CarreraForm.php
│   ├── MateriaForm.php
│   ├── GrupoForm.php
│   ├── NotaExamenForm.php
│   └── PostulacionForm.php
│
├── Admin/                         # Módulo de Administración
│   ├── Carrera/
│   │   ├── Index.php              # Página completa (Listado + Búsqueda)
│   │   ├── Detalle.php
│   │   └── FormularioModal.php    # Subcomponente para Crear/Editar
│   ├── Docente/
│   │   ├── Index.php
│   │   └── AsignarGrupoModal.php
│   ├── Grupo/
│   │   ├── Index.php
│   │   └── FormarGruposAutomaticos.php
│   └── Dashboard.php
│
├── Docente/                       # Módulo del Docente
│   ├── Grupo/
│   │   ├── Listado.php
│   │   └── Detalle.php
│   ├── Nota/
│   │   └── RegistroCalificaciones.php
│   └── Dashboard.php
│
├── Postulante/                    # Módulo del Postulante
│   ├── FormularioPostulacion.php
│   ├── EstadoAdmision.php
│   └── Dashboard.php
│
└── Shared/                        # Componentes Reutilizables / UI Genérica
    ├── BuscadorGlobal.php
    └── Notificaciones.php
```

---

### 2. Convención de Nombres y Estilo

*   **Páginas Completas (`Full-page Components`):** Nombres en inglés o español según se elija, pero consistentes. Usar `Index`, `Create`, `Edit`, `Show` dentro del directorio del módulo (ej: `Admin\Carrera\Index`). Mapeados directamente en `routes/web.php` usando `Route::get('/carreras', Admin\Carrera\Index::class)`.
*   **Subcomponentes / Modales:** Nombres descriptivos con sufijo (ej: `FormularioModal.php`, `SelectorCarreras.php`).
*   **Vistas Blade correspondientes:** Ubicadas en `resources/views/livewire/[modulo]/[nombre-componente].blade.php` en minúsculas y usando kebab-case (ej: `resources/views/livewire/admin/carrera/index.blade.php`).

---

### 3. Patrón Form Objects (Evitar Componentes Gigantes)

Para mantener la lógica de validación e interacción separada del renderizado, **toda acción de formulario debe encapsularse en un Form Object**.

#### Ejemplo: `app/Livewire/Forms/CarreraForm.php`
```php
namespace App\Livewire\Forms;

use App\Models\Carrera;
use Livewire\Form;
use Livewire\Attributes\Rule;

class CarreraForm extends Form
{
    public ?Carrera $carrera = null;

    #[Rule('required|min:3|max:100|unique:carreras,nombre')]
    public string $nombre = '';

    #[Rule('required|min:2|max:5|unique:carreras,sigla')]
    public string $sigla = '';

    public function setCarrera(Carrera $carrera)
    {
        $this->carrera = $carrera;
        $this->nombre = $carrera->nombre;
        $this->sigla = $carrera->sigla;
    }

    public function store()
    {
        $this->validate();
        Carrera::create($this->all());
        $this->reset();
    }

    public function update()
    {
        $this->validate([
            'nombre' => 'required|min:3|max:100|unique:carreras,nombre,' . $this->carrera->id,
            'sigla' => 'required|min:2|max:5|unique:carreras,sigla,' . $this->carrera->id,
        ]);

        $this->carrera->update($this->all());
    }
}
```

---

### 4. Componentes de Tablas Eficientes

Las tablas deben cargarse de forma reactiva y soportar paginación, filtros y ordenamiento en el servidor.

#### Ejemplo de Estructura de Tabla: `app/Livewire/Admin/Carrera/Index.php`
```php
namespace App\Livewire\Admin\Carrera;

use App\Models\Carrera;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    // Listener para refrescar la tabla cuando se crea/edita una carrera
    protected $listeners = ['carreraSaved' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage(); // Resetea la paginación al buscar
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function render()
    {
        $carreras = Carrera::query()
            ->where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('sigla', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.carrera.index', compact('carreras'));
    }
}
```

---

### 5. Integración con Laravel Flux (Modales y Componentes Premium)

Laravel Flux facilita el renderizado de UI moderna. La interacción con modales debe ser declarativa y libre de JS innecesario.

#### Estructura Blade de la Vista con Laravel Flux (`resources/views/livewire/admin/carrera/index.blade.php`)
```html
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Gestión de Carreras</flux:heading>
        
        <!-- Botón que abre el modal de Flux de manera declarativa -->
        <flux:modal.trigger name="carrera-form-modal">
            <flux:button variant="primary" icon="plus">Nueva Carrera</flux:button>
        </flux:modal.trigger>
    </div>

    <!-- Filtro de Búsqueda -->
    <div class="w-full md:w-1/3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar carrera o sigla..." icon="magnifying-glass" />
    </div>

    <!-- Tabla Flux -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable wire:click="sortBy('nombre')">Nombre</flux:table.column>
            <flux:table.column sortable wire:click="sortBy('sigla')">Sigla</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($carreras as $carrera)
                <flux:table.row :key="$carrera->id">
                    <flux:table.cell class="font-medium">{{ $carrera->nombre }}</flux:table.cell>
                    <flux:table.cell>{{ $carrera->sigla }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="$dispatch('editCarrera', { id: {{ $carrera->id }} })" />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    {{ $carreras->links() }}

    <!-- Inclusión del componente Modal del Formulario (Encapsulado para no engordar el Index) -->
    <livewire:admin.carrera.formulario-modal />
</div>
```

---

### 6. Comunicación Limpia entre Componentes (Eventos)

Para evitar acoplamiento, los componentes se comunican enviando eventos.
*   **De Subcomponente a Padre:** `formulario-modal` despacha un evento global `carreraSaved`. El componente `Index` escucha el evento y refresca su listado (`$refresh`).
*   **De Padre a Subcomponente:** El botón de edición en la tabla despacha `editCarrera` pasando el `id`. El modal escucha el evento, carga la carrera en su Form Object y se abre.

#### Ejemplo de Recepción de Evento en el Modal (`app/Livewire/Admin/Carrera/FormularioModal.php`)
```php
namespace App\Livewire\Admin\Carrera;

use App\Models\Carrera;
use App\Livewire\Forms\CarreraForm;
use Livewire\Component;
use Livewire\Attributes\On;

class FormularioModal extends Component
{
    public CarreraForm $form;
    public bool $isOpen = false;

    #[On('editCarrera')]
    public function loadCarrera(int $id)
    {
        $carrera = Carrera::findOrFail($id);
        $this->form->setCarrera($carrera);
        $this->isOpen = true; // Abre el modal de Flux
    }

    public function save()
    {
        if ($this->form->carrera) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        $this->isOpen = false;
        
        // Notificar al listado para refrescar
        $this->dispatch('carreraSaved');
        
        // Despachar alerta de éxito de Flux
        $this->dispatch('notify', variant: 'success', message: 'Carrera guardada correctamente.');
    }
}
```

---

### 7. Principios Clave de Escalabilidad y Performance

1.  **Lazy Loading:** Para dashboards con consultas pesadas de base de datos (como reportes estadísticos o listados extensos de notas), utilizar la directiva `#[Lazy]` de Livewire 3 para renderizar el esqueleto del panel instantáneamente y cargar los datos pesados de forma asíncrona.
2.  **No almacenar modelos completos en propiedades públicas:** En lugar de `public User $user`, utilizar `public int $userId`. Almacenar modelos completos expone datos al cliente y aumenta drásticamente la carga útil de Livewire (payload).
3.  **Encapsulación de Modales de Edición:** Mantener los modales de creación/edición en archivos de componentes separados, en lugar de escribirlos directamente dentro del listado principal. Esto reduce el tamaño de las clases y hace que las plantillas Blade sean mucho más legibles.
