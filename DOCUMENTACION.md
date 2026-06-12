# INFORME TÉCNICO DE EXAMEN - SISTEMAS DE INFORMACIÓN II
## SISTEMA DE ADMISIÓN UNIVERSITARIA Y CONTROL DE CUPOS (CUP)

---

### CARÁTULA
*   **Institución:** Universidad Autónoma Gabriel René Moreno (UAGRM)
*   **Facultad:** Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones (FICCT)
*   **Carrera:** Ingeniería de Sistemas / Ingeniería Informática
*   **Materia:** Sistemas de Información II
*   **Semestre:** I-2026
*   **Evaluación:** Segundo Parcial
*   **Proyecto:** Sistema Universitario de Admisión, Registro, Grupos y Selección Automática de Admitidos por Cupo (CUP)

---

## ÍNDICE GENERAL
1.  **Capítulo 1: Introducción y Objetivos**
    *   1.1. Introducción
    *   1.2. Antecedentes del Problema
    *   1.3. Objetivo General
    *   1.4. Objetivos Específicos
    *   1.5. Alcance del Proyecto
    *   1.6. Exclusiones del Alcance
2.  **Capítulo 2: Ingeniería de Requerimientos**
    *   2.1. Requerimientos Funcionales (RF)
    *   2.2. Requerimientos No Funcionales (RNF)
    *   2.3. Actores del Sistema
    *   2.4. Riesgos y Plan de Mitigación
3.  **Capítulo 3: Análisis Funcional y Reglas de Negocio**
    *   3.1. Reglas de Negocio Críticas
    *   3.2. Casos Límite (Edge Cases) y Soluciones
    *   3.3. Glosario de Términos del CUP
4.  **Capítulo 4: Diseño de Arquitectura y Tecnologías**
    *   4.1. Stack Tecnológico Utilizado
    *   4.2. Patrón de Diseño y Arquitectura (Laravel + Livewire MVVM)
    *   4.3. Organización y Estructura de Capas
5.  **Capítulo 5: Modelo de Persistencia y Estructura de Datos**
    *   5.1. Esquema Relacional de Base de Datos
    *   5.2. Diccionario de Datos Sintético
    *   5.3. Control de Concurrencia y Consistencia
6.  **Capítulo 6: Implementación del Sistema y Seguridad**
    *   6.1. Organización del Código Base (Controllers, Livewire Components, Services)
    *   6.2. Seguridad y Control de Acceso (RBAC - Spatie)
    *   6.3. Algoritmo de Admisión y Reasignación por Cupos
    *   6.4. Controladores de Exportación y KPIs
7.  **Capítulo 7: Plan de Pruebas y Verificación**
    *   7.1. Estrategia de Pruebas Unitarias y de Integración
    *   7.2. Cobertura del Suite de Pruebas (AdminCrudTest)
    *   7.3. Resultados del Proceso de Verificación

---

## CAPÍTULO 1: INTRODUCCIÓN Y OBJETIVOS

### 1.1. Introducción
El presente documento describe el diseño, la arquitectura y la implementación del Sistema de Admisión Universitaria (CUP), una plataforma web responsiva orientada a la automatización integral de la gestión académica de cursos preuniversitarios, incluyendo la inscripción, la conformación automática de grupos de estudiantes, la carga docente de calificaciones ponderadas, y la selección descendente por méritos académicos para la adjudicación de cupos límite de primera y segunda opción.

### 1.2. Antecedentes del Problema
Tradicionalmente, los procesos de admisión de estudiantes a la educación superior mediante la modalidad de cursos preuniversitarios sufren de ineficiencias críticas: el agrupamiento manual de miles de postulantes en aulas es lento y desigual; la gestión de calificaciones es propensa a errores de cálculo; y la adjudicación de plazas según cupos disponibles en varias opciones carece de transparencia y agilidad. Este sistema soluciona dichos problemas garantizando transparencia absoluta por mérito académico.

### 1.3. Objetivo General
Diseñar y desarrollar un sistema de información web transaccional y responsivo que automatice el ciclo de vida del proceso preuniversitario (CUP), optimizando la distribución de estudiantes en grupos, la gestión de calificaciones ponderadas y la selección justa y veloz de admitidos por cupo para carreras de primera y segunda opción.

### 1.4. Objetivos Específicos
*   Implementar un modelo de datos robusto e íntegro bajo PostgreSQL que garantice transacciones ACID.
*   Desarrollar un panel administrativo dinámico (Dashboard) que concentre indicadores clave (KPIs) de postulantes, aprobados, reprobados, grupos habilitados y ocupación de cupos.
*   Implementar el algoritmo automatizado de formación de grupos académicos y asignación docente.
*   Desarrollar el servicio automático de ranking y selección de admitidos en base a cupos disponibles.
*   Incorporar buscadores reactivos por voz con traducción automática de números y palabras clave para mejorar la accesibilidad administrativa.
*   Permitir la exportación rápida de listados y promedios en formato CSV compatible con Microsoft Excel.

### 1.5. Alcance del Proyecto
El sistema abarca el back-office administrativo (carreras, materias, docentes, exámenes, grupos, calificaciones y admisiones) y el portal del postulante (consulta de grupo, notas y resultados de admisión).

### 1.6. Exclusiones del Alcance
Quedan excluidas las pasarelas de pago integradas en línea, el registro de asistencia diaria de alumnos y la creación de aplicaciones nativas para móviles Android/iOS (cubierto a través de diseño responsivo).

---

## CAPÍTULO 2: INGENIERÍA DE REQUERIMIENTOS

### 2.1. Requerimientos Funcionales (RF)
*   **RF-01 (Gestión de Carreras):** Creación y mantenimiento de carreras universitarias indicando cupos de primera y segunda opción.
*   **RF-02 (Gestión de Materias):** Mantenimiento de planes de materias por carrera.
*   **RF-03 (Gestión de Docentes):** Administración de docentes y asignaciones a materias.
*   **RF-04 (Auto-registro de Postulantes):** Formulario de registro de datos del estudiante.
*   **RF-05 (Selección de Opciones):** Elección obligatoria de Primera Opción de carrera y opcional de Segunda Opción.
*   **RF-06 (Formación de Grupos):** Algoritmo automático de formación homogénea de grupos académicos.
*   **RF-07 (Asignación Docente):** Vinculación de docentes con sus grupos.
*   **RF-08 (Carga de Notas):** Registro docente de puntajes individuales en evaluaciones.
*   **RF-09 (Selección y Admisión):** Proceso masivo que ejecuta el ranking descendente y asigna plazas según cupos.
*   **RF-10 (Reportes y Exportación):** Descarga rápida en CSV de listas de inscritos y admitidos por gestión.

### 2.2. Requerimientos No Funcionales (RNF)
*   **RNF-01 (Diseño Responsivo):** Interfaz web moderna adaptable construida con TailwindCSS y Livewire.
*   **RNF-02 (Base de Datos):** Base de datos relacional PostgreSQL con integridad referencial.
*   **RNF-03 (Seguridad):** Autenticación y control de acceso basado en roles (RBAC) con contraseñas encriptadas mediante bcrypt.
*   **RNF-04 (Rendimiento):** Consultas optimizadas con relaciones precargadas para prevenir el problema N+1 en vistas de reportes masivos.

### 2.3. Actores del Sistema
1.  **Administrador:** Configura parámetros, forma grupos, ejecuta admisiones, y exporta reportes.
2.  **Docente:** Registra calificaciones de exámenes asignados.
3.  **Postulante:** Registra datos, verifica notas de materias, grupo, y estado de ingreso.

### 2.4. Riesgos y Plan de Mitigación
*   **Concurrencia en adjudicación:** Riesgo de sobrepasar cupos si dos administradores corren el proceso simultáneamente. *Mitigación:* Se implementa bloqueo a nivel de transacciones en base de datos.
*   **Falsificación de notas:** *Mitigación:* El sistema bloquea las notas a nivel de docente tras procesar la admisión oficial.

---

## CAPÍTULO 3: ANÁLISIS FUNCIONAL Y REGLAS DE NEGOCIO

### 3.1. Reglas de Negocio Críticas
*   **RN-01 (Nota de Aprobación):** La nota mínima ponderada para ser elegible de ingreso por cupo es **60/100 puntos** (o la definida académicamente).
*   **RN-02 (Suma de Exámenes):** La suma de las ponderaciones de los exámenes de una materia debe ser exactamente **100%** para calcular una nota final.
*   **RN-03 (Prelación de Opción):** El algoritmo de segunda opción se ejecuta únicamente tras completar y cerrar la asignación en primera opción para todas las carreras.
*   **RN-04 (Exclusividad de Grupo):** Un postulante pertenece a un único grupo por materia.
*   **RN-05 (Requisitos de Contratación de Docentes):** De acuerdo con las normas de la FICCT, un docente solo puede ser asignado a impartir clases (entre 1 y 4 grupos máximo) si cumple con los requisitos obligatorios de ser profesional en el área, poseer maestría, y diplomado en educación superior.

### 3.2. Casos Límite (Edge Cases) y Soluciones
*   **Empates en el límite de cupos:** Si hay empates en la última vacante, el sistema ordena secundariamente por mayor puntaje en el Examen Final, y en última instancia por fecha de registro del postulante.
*   **Postulantes sin notas:** Se asigna un promedio ponderado de 0, y su estado final pasa automáticamente a "Reprobado".

### 3.3. Glosario de Términos
*   **CUP:** Curso Universitario de Preparación.
*   **Cupo Remanente:** Plazas disponibles en una carrera después de admitir a los postulantes de primera opción.
*   **Reasignado:** Postulante admitido en su carrera de segunda opción al no conseguir cupo en la primera.

---

## CAPÍTULO 4: DISEÑO DE ARQUITECTURA Y TECNOLOGÍAS

### 4.1. Stack Tecnológico
*   **Framework:** Laravel 12 (PHP 8.2+)
*   **Capa Frontend:** Livewire v3 (Reactividad en el servidor) + Blade Templates
*   **Diseño Visual:** Tailwind CSS v4 + Laravel Flux (Biblioteca premium de componentes UI)
*   **Base de Datos:** PostgreSQL
*   **Roles:** Spatie Laravel-Permission

### 4.2. Patrón de Diseño
Se utiliza el patrón **MVVM** (Model-View-ViewModel) provisto por Livewire. El componente de Livewire actúa como la capa ViewModel, enlazando reactivamente las vistas Blade con los Modelos de base de datos sin recargar la página.

### 4.3. Organización y Estructura de Capas
La arquitectura está dividida en:
*   **Capa de Presentación:** Vistas Blade responsivas integradas con componentes reactivos de Livewire y Flux.
*   **Capa de Negocio (Servicios/Actions):** Clases de servicios dedicadas (como `AdmissionSelectionService`) para encapsular las reglas complejas de asignación.
*   **Capa de Persistencia:** Modelos Eloquent relacionados (`Postulante`, `Carrera`, `Gestion`, `Grupo`, `Examen`, `Nota`).

---

## CAPÍTULO 5: MODELO DE PERSISTENCIA Y ESTRUCTURA DE DATOS

### 5.1. Esquema Relacional de Base de Datos
El sistema implementa las siguientes tablas vinculadas con llaves foráneas:
*   `users`: Cuentas de acceso e información personal.
*   `carreras`: Registra facultades y cupos.
*   `materias`: Planes de estudio asociados a carreras.
*   `gestiones`: Periodos semestrales (ej: I-2026).
*   `docentes`: Cuerpo docente asignado.
*   `postulantes`: Registro transaccional de aspirantes, notas finales y estado de admisión.
*   `grupos`: Distribución de alumnos y docentes por materia.
*   `examenes`: Tipos de exámenes y ponderación (1ra/2da/Final).
*   `notas`: Calificaciones de postulantes en exámenes.

### 5.2. Control de Concurrencia y Consistencia
Para evitar condiciones de carrera, el cálculo de las proyecciones y asignación de admitidos se ejecuta dentro de transacciones de base de datos (`DB::transaction`) con bloqueos de fila (`lockForUpdate`) para evitar la venta de cupos fantasmas.

---

## CAPÍTULO 6: IMPLEMENTACIÓN DEL SISTEMA Y SEGURIDAD

### 6.1. Organización del Código Base
*   `app/Livewire/Admin/Dashboard.php`: Controlador del dashboard transaccional y visualización de gráficos.
*   `app/Services/AdmissionSelectionService.php`: Implementa el cálculo de promedios, rankings y adjudicación por cupos.
*   `app/Http/Controllers/ReportExportController.php`: Genera archivos CSV optimizados para Excel (con BOM UTF-8) mediante streams.

### 6.2. Seguridad y Control de Acceso
El sistema bloquea el acceso a rutas administrativas mediante middlewares. Se configuran tres roles con permisos precisos mediante Spatie Permission:
*   `Administrador`: Acceso total.
*   `Docente`: Acceso restringido a calificaciones de sus grupos.
*   `Postulante`: Acceso de lectura de notas propias.

### 6.3. Algoritmo de Admisión
El motor de admisión funciona en dos fases:
1.  **Fase 1 (Primera Opción):** Filtra estudiantes aprobados (nota >= 60) que seleccionaron la carrera como primera opción, los ordena de mayor a menor y asigna plazas hasta el límite `cupo_primera_opcion`.
2.  **Fase 2 (Segunda Opción):** Identifica carreras con cupos vacíos, agrupa postulantes aprobados no admitidos en su primera opción que colocaron esa carrera como segunda opción, los ordena de mayor a menor, y asigna las plazas remanentes.

---

## CAPÍTULO 7: PLAN DE PRUEBAS Y VERIFICACIÓN

### 7.1. Estrategia de Pruebas
Se implementó un conjunto de pruebas funcionales automatizadas utilizando la suite nativa de Laravel/PHPUnit (`tests/Feature/AdminCrudTest.php`). Las pruebas simulan interacciones del navegador y flujos transaccionales.

### 7.2. Cobertura del Suite de Pruebas (AdminCrudTest)
Las pruebas cubren los siguientes escenarios principales:
*   Eliminación en cascada de docentes y sus usuarios asociados.
*   Creación, edición y borrado de postulantes bajo transacciones.
*   Validaciones de límites y ponderación máxima en exámenes (sumatoria de peso <= 100%).
*   Recálculo de puntajes académicos al cambiar notas.
*   Filtros y consultas por voz (procesando cadenas como "nota mayor a 75", "nota final 50" y números textuales como "cincuenta").
*   Limpieza automática de filtros tanto en frontend (UI) como en backend.

### 7.3. Resultados del Proceso de Verificación
El suite de pruebas ejecuta satisfactoriamente un total de **56 casos de prueba** con **301 aserciones de integridad** (incluyendo la validación de requerimientos de contratación docente, cupos y admisiones por segunda opción), obteniendo un 100% de éxito en el entorno de desarrollo y pruebas.
