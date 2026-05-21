# Guía de Configuración Técnica Inicial
## Sistema de Admisión Universitaria (CUP)

**Rol:** Desarrollador Senior Laravel 12  
**Estado de la Base del Proyecto:** **¡Ya está creada!** El proyecto base de Laravel 12.x ha sido inicializado exitosamente en el directorio raíz.

A continuación, se detalla la arquitectura, dependencias y comandos exactos para configurar la pila tecnológica solicitada (PostgreSQL + Livewire + TailwindCSS + Flux + Spatie + Auth).

---

### 1. Dependencias Necesarias y Comandos de Instalación

Ejecuta los siguientes comandos en orden desde la raíz del proyecto para instalar la suite de herramientas:

```bash
# 1. Instalar Laravel Breeze (Kit de Autenticación con soporte para Livewire)
composer require laravel/breeze --dev

# 2. Instalar el scaffold de Breeze con Livewire y TailwindCSS funcional
php artisan breeze:install livewire --dark --typescript

# 3. Instalar Laravel Flux (Biblioteca de componentes de Caleb Porzio)
composer require livewire/flux

# 4. Instalar Spatie Laravel-Permission (Roles y Permisos)
composer require spatie/laravel-permission
```

---

### 2. Configuración PostgreSQL

Laravel 12 viene preparado para SQLite por defecto. Para migrar a **PostgreSQL**, realiza los siguientes cambios:

#### Modificación en `.env`
Edita la sección de base de datos en tu archivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cup_admision
DB_USERNAME=tu_usuario_postgres
DB_PASSWORD=tu_contrasena_postgres
```

#### Modificación en `config/database.php` (Por defecto ya viene configurado, pero es importante validar)
El driver `pgsql` debe apuntar al esquema `public` por defecto y tener activada la preparación de consultas.

---

### 3. Configuración Tailwind CSS & Laravel Flux

Laravel 12 utiliza **Tailwind CSS v4** con integración nativa en Vite. 

#### Importación de Estilos de Flux
Debes indicarle a Tailwind que procese los estilos de Flux UI. Edita tu archivo de estilos principal `resources/css/app.css`:

```css
@import "tailwindcss";

/* Importar estilos de Laravel Flux */
@import "../../vendor/livewire/flux/dist/flux.css";
```

#### Directivas en las Plantillas Blade
Modifica tu archivo de layout principal (ej. `resources/views/components/layouts/app.blade.php` o `layouts/app.blade.php` creado por Breeze):

Añade las siguientes directivas dentro de las etiquetas correspondientes:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'CUP Admisión' }}</title>

        <!-- Cargar estilos y fuentes -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Directiva de Apariencia de Flux (Soporte Dark Mode) -->
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-950 dark:text-white">
        
        <!-- Tu layout de Flux va aquí -->
        {{ $slot }}

        <!-- Scripts de Flux -->
        @fluxScripts
    </body>
</html>
```

---

### 4. Configuración de Livewire

Livewire se configura automáticamente en Laravel 12. Sin embargo, para producción y despliegue en nube (donde las peticiones pueden pasar por balanceadores de carga o proxies reversos), debes asegurar lo siguiente:

#### Configuración de subida de archivos (si se requiere para requisitos digitales):
Asegura en `.env` que el disco sea compatible con la nube (ej. AWS S3 o compatible):

```env
FILESYSTEM_DISK=s3
```

---

### 5. Configuración de Roles y Permisos (Spatie)

Para habilitar la gestión de roles y permisos a nivel de base de datos, ejecuta el comando de publicación de Spatie:

```bash
# Publicar el archivo de configuración y la migración
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Esto generará la migración en `database/migrations/xxxx_xx_xx_xxxxxx_create_permission_tables.php`. 

#### Carga en el Modelo User
Debes agregar el Trait `HasRoles` al modelo `App\Models\User` (`app/Models/User.php`):

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <-- Importar

class User extends Authenticatable
{
    use Notifiable, HasRoles; // <-- Usar Trait

    // ... resto del modelo
}
```

---

### 6. Estructura Recomendada del Proyecto (Escalable y Clean Architecture)

Para evitar la "arquitectura improvisada" (como poner lógica de negocio pesada dentro de los controladores o de los componentes Livewire), se recomienda el patrón **Actions** combinado con **Repositories** o **Services**.

```
app/
├── Actions/                  # Lógica de negocio de caso único (Single Responsibility)
│   ├── Postulante/
│   │   ├── RegistrarPostulanteAction.php
│   │   └── ReasignarSegundaCarreraAction.php
│   ├── Grupo/
│   │   └── FormarGruposAutomaticamenteAction.php
│   └── Admision/
│       └── ProcesarAdmitidosCarreraAction.php
│
├── Services/                 # Servicios que integran múltiples llamadas de negocio o integraciones API
│
├── Livewire/                 # Componentes de UI (Thin Components)
│   ├── Postulante/
│   │   ├── FormularioPostulacion.php
│   │   └── ConsultaResultados.php
│   ├── Docente/
│   │   └── RegistroNotas.php
│   └── Admin/
│       ├── Dashboard.php
│       └── GestionGrupos.php
│
├── Models/                   # Modelos Eloquent con relaciones limpias
│   ├── Postulante.php
│   ├── Carrera.php
│   ├── Materia.php
│   ├── Grupo.php
│   └── Examen.php
│
database/
├── migrations/               # Migraciones PostgreSQL relacionales
└── seeders/                  # Seeders para Roles (Admin, Docente, Postulante) y Datos Maestros
```

**¿Por qué usar Actions?**
- **Testabilidad:** Cada acción realiza una sola cosa (ej. `FormarGruposAutomaticamenteAction`). Puedes escribir tests unitarios enfocados sin pasar por HTTP o Livewire.
- **Reusabilidad:** Si en el futuro se requiere una API REST o un comando CLI (`Artisan`), simplemente llamas al mismo `Action`.
- **Mantenibilidad:** Los componentes Livewire solo se encargan de manejar el estado de la UI y delegar la lógica pesada a las clases Action.

---

### 7. Variables Críticas en el Archivo `.env` (Preparación para Nube)

Asegúrate de configurar los siguientes parámetros en producción/nube:

```env
APP_NAME="CUP Admisión Universitaria"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://admision.tuuniversidad.edu

# Configuración SSL en Nube / Balanceadores de carga
# (Forzar HTTPS en entorno de producción)
ASSET_URL=https://admision.tuuniversidad.edu

# Base de datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=tu-rds-postgres.amazonaws.com
DB_PORT=5432
DB_DATABASE=cup_prod
DB_USERNAME=admin_user
DB_PASSWORD=contrasena_segura

# Colas y Caché para alto rendimiento
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

# Configuración del Mailer (Notificación de admisión)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@tuuniversidad.edu
MAIL_PASSWORD=mailgun_password
```
