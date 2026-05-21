# Documento de Especificación de Requerimientos y Análisis Funcional
## Sistema de Admisión Universitaria (CUP)

**Autor:** Analista de Sistemas Senior  
**Estado:** Propuesto para revisión  
**Tecnología Objetivo:** PHP + PostgreSQL (Web Responsivo, Nube)  
**Metodología:** Proceso Unificado con modelado UML  

---

### 1. Requerimientos Funcionales (RF)

#### Módulo de Configuración y Datos Maestros
*   **RF-01: Gestión de Carreras:** El sistema debe permitir registrar, modificar, dar de baja lógica y listar carreras, especificando nombre, facultad, cupos máximos de admisión para primera opción y cupos máximos para segunda opción.
*   **RF-02: Gestión de Materias:** El sistema debe permitir registrar, modificar y listar las materias correspondientes al curso de admisión (CUP), asociándolas a las diferentes carreras (una materia puede pertenecer a una o más carreras).
*   **RF-03: Gestión de Docentes:** El sistema debe permitir el registro y mantenimiento de los docentes habilitados para el CUP, incluyendo datos personales, de contacto y especialidad de materias.

#### Módulo de Registro y Postulación
*   **RF-04: Auto-registro de Postulantes:** El sistema debe permitir a los aspirantes registrarse en la plataforma ingresando sus datos personales (Cédula de Identidad, nombres, apellidos, correo electrónico, teléfono, etc.) y adjuntar requisitos digitales básicos.
*   **RF-05: Selección de Carreras (Opciones):** Al postularse, el sistema debe exigir que el postulante elija obligatoriamente una carrera como **Primera Opción** y, de manera opcional, una carrera como **Segunda Opción** (sujeta a disponibilidad de cupos remanentes).

#### Módulo de Organización Académica
*   **RF-06: Formación Automática de Grupos:** El sistema debe agrupar automáticamente a los postulantes inscritos en base a dos parámetros configurables:
    *   Cantidad máxima de alumnos por grupo.
    *   Cantidad mínima de alumnos por grupo.
    El algoritmo debe distribuir homogéneamente a los postulantes por materia.
*   **RF-07: Asignación de Docentes a Grupos:** El Administrador debe poder asignar uno o varios docentes a los grupos previamente conformados para dictar las respectivas materias.

#### Módulo de Evaluación y Notas
*   **RF-08: Configuración de Exámenes:** El sistema debe permitir la creación de exámenes asociados a cada materia, definiendo su fecha, tipo (parcial, final, recuperatorio) y la ponderación (porcentaje de la nota final, sumando 100% en total).
*   **RF-09: Registro y Carga de Notas:** El sistema debe permitir a los docentes asignados registrar y modificar las notas de los exámenes de los alumnos que pertenezcan a sus grupos asignados.
*   **RF-10: Cálculo de Estado de Aprobación:** El sistema debe calcular automáticamente la nota final ponderada de cada postulante por materia y carrera, determinando su estado académico como **Aprobado** (si su promedio final es mayor o igual a la nota mínima de aprobación parametrizada) o **Reprobado**.

#### Módulo de Admisión y Selección
*   **RF-11: Selección de Admitidos por Cupo (Primera Opción):** El sistema debe ejecutar un proceso de asignación de plazas ordenando de manera descendente (de mayor a menor nota) a todos los postulantes **aprobados** que eligieron la carrera como Primera Opción, otorgando el estado de **Admitido** hasta agotar el cupo definido.
*   **RF-12: Proceso de Reasignación (Segunda Opción):** Para aquellas carreras que aún tengan cupos disponibles tras el proceso de Primera Opción, el sistema debe permitir reasignar postulantes ordenándolos de manera descendente (que estén aprobados pero no hayan obtenido cupo en su Primera Opción) y que hayan seleccionado esta carrera como Segunda Opción.
*   **RF-13: Publicación de Resultados:** El sistema debe permitir la consulta pública de la lista de postulantes admitidos por carrera, ocultando datos sensibles pero mostrando nombres, Cédula de Identidad enmascarada, nota final y estado (Admitido / No Admitido / Reasignado).

#### Módulo de Reportes e Indicadores
*   **RF-14: Reportes Estadísticos de Admisión:** Generación de reportes dinámicos que presenten:
    *   Cantidad de inscritos vs. cantidad de admitidos por carrera.
    *   Porcentaje de aprobación/reprobación por materia y grupo.
    *   Estadísticas de rendimiento de estudiantes según grupo y docente asignado.
    *   Reporte de cupos remanentes post-admisión de primera opción.

---

### 2. Requerimientos No Funcionales (RNF)

*   **RNF-01: Usabilidad y Diseño Responsivo:** La interfaz de usuario debe ser totalmente responsiva (adaptable a laptops, desktops y tablets), utilizando estándares HTML5, CSS3 modernos y asegurando una experiencia fluida e intuitiva sin depender de una aplicación móvil nativa.
*   **RNF-02: Base de Datos Relacional:** Toda la persistencia de datos debe ser gestionada utilizando el motor de base de datos **PostgreSQL**, aprovechando transacciones ACID para evitar inconsistencias en procesos críticos como el cálculo masivo de notas e inserción de admitidos.
*   **RNF-03: Rendimiento y Concurrencia:** El sistema debe estar optimizado para soportar al menos 500 solicitudes concurrentes en periodos de consulta de notas y resultados, con un tiempo de respuesta de carga inferior a 2 segundos para páginas estáticas y 4 segundos para procesamiento de reportes.
*   **RNF-04: Seguridad y Control de Acceso:** Se implementará un control de acceso basado en roles (RBAC - Role-Based Access Control) con contraseñas encriptadas mediante algoritmos seguros (como bcrypt). La sesión de usuario debe expirar tras 15 minutos de inactividad.
*   **RNF-05: Despliegue en la Nube:** La arquitectura del software debe permitir el despliegue modular en plataformas en la nube (ej. Heroku, AWS, DigitalOcean), facilitando el escalado horizontal de la base de datos y del servidor web PHP.
*   **RNF-06: Estándares de Modelado:** Se utilizarán diagramas estándar UML (Casos de Uso, Clases, Secuencia, Actividades) para guiar la fase de desarrollo bajo las directrices del Proceso Unificado de Desarrollo (UP).

---

### 3. Actores del Sistema

1.  **Postulante:**
    *   *Descripción:* Persona interesada en ingresar a la universidad que se registra en la plataforma.
    *   *Acciones clave:* Auto-registro, selección de carreras (1ra y 2da opción), consulta de su grupo asignado, consulta de notas obtenidas y verificación de su estado final de admisión.
2.  **Docente:**
    *   *Descripción:* Profesional encargado de dictar las materias del CUP y evaluar a los estudiantes.
    *   *Acciones clave:* Consulta de listas de alumnos por grupo asignado, registro y modificación de notas en exámenes asignados a su materia y grupo.
3.  **Administrador de Admisiones:**
    *   *Descripción:* Usuario del personal administrativo con control global de la configuración del sistema.
    *   *Acciones clave:* Gestión de carreras, materias y docentes; parametrización de capacidades de grupo y notas mínimas; ejecución del algoritmo de formación de grupos y del proceso automático de selección de admitidos; reasignación manual o automática a segunda opción; generación de reportes globales y auditoría.

---

### 4. Reglas de Negocio (RN)

*   **RN-01: Exclusividad de Postulación:** Un postulante solo puede registrar una única postulación por convocatoria del CUP. No puede inscribirse varias veces con el mismo identificador (Cédula de Identidad o Correo Electrónico).
*   **RN-02: Prelación de Selección por Nota:** La selección de admitidos se realiza estrictamente en orden descendente de la nota promedio ponderada acumulada en el CUP. Ningún postulante con una nota inferior puede ser admitido si existe uno con mayor nota en la lista de espera de esa opción.
*   **RN-03: Nota Mínima de Aprobación:** Para ser elegible en el proceso de selección y adjudicación de cupos (tanto para primera como segunda opción), el postulante debe alcanzar una nota final promedio ponderada igual o mayor a **51/100 puntos** (o la nota mínima parametrizada por el administrador). Los reprobados quedan automáticamente descartados de cualquier cupo.
*   **RN-04: Prioridad Absoluta de la Primera Opción:** El proceso de reasignación a la segunda opción solo se ejecutará **después** de haber completado y cerrado la selección de admitidos de primera opción en todas las carreras. Los cupos remanentes disponibles para segunda opción son el resultado de la capacidad total menos los postulantes admitidos en primera opción.
*   **RN-05: Relación de Grupo:** Un postulante solo puede pertenecer a un único grupo académico por materia durante la gestión vigente del CUP.
*   **RN-06: Ponderación Total de Evaluaciones:** La suma de las ponderaciones de todos los exámenes configurados para una materia en el CUP debe ser exactamente igual al **100%**. El sistema no permitirá publicar la nota final si la configuración de exámenes no cumple esta condición.

---

### 5. Casos Límite Importantes (Edge Cases)

*   **Empates en el Último Cupo Disponible:**
    *   *Escenario:* La carrera "Medicina" ofrece 50 cupos. Los postulantes en la posición 50 y 51 tienen exactamente la misma nota final aprobatoria (ej. 78.50).
    *   *Resolución Propuesta:* El sistema debe aplicar un criterio de desempate automatizado parametrizable:
        1.  Mayor nota obtenida en el examen final.
        2.  Fecha y hora de finalización del registro de inscripción (prioridad al que se registró antes).
        3.  Aprobación extraordinaria autorizada por el Administrador (ampliación de cupo en 1 unidad).
*   **Grupos con Cantidades de Alumnos Fuera del Rango:**
    *   *Escenario:* Se define que la cantidad mínima de alumnos por grupo es 20 y la máxima es 40. Al realizar la formación automática, quedan 5 alumnos sobrantes que no completan el mínimo para formar un nuevo grupo.
    *   *Resolución Propuesta:* El algoritmo debe distribuir equitativamente a los alumnos restantes entre los grupos ya formados, superando temporalmente la capacidad máxima (ej. grupos de 41 alumnos), o notificar al Administrador para una reasignación manual de aula.
*   **Postulantes Incompletos al Cierre de Evaluaciones:**
    *   *Escenario:* Un estudiante no se presentó a ninguno de los exámenes del CUP.
    *   *Resolución Propuesta:* Su nota final ponderada se calculará como 0. Su estado final será "Reprobado" y quedará excluido del proceso de selección y de la lista de reasignación de segunda opción.
*   **Modificación de Notas Post-Publicación de Resultados de Admisión:**
    *   *Escenario:* Un docente cometió un error al transcribir una nota y solicita la corrección después de que las listas de admitidos ya fueron publicadas.
    *   *Resolución Propuesta:* El sistema debe bloquear la edición de notas a nivel docente tras la publicación de resultados. Cualquier modificación posterior requerirá una solicitud formal de auditoría y solo podrá ser realizada por el Administrador de Admisiones, recalculando las listas de admisión afectadas en cascada.

---

### 6. Riesgos del Proyecto

1.  **Riesgo de Rendimiento en Procesamiento Masivo (Cálculos Concurrentes):** Al finalizar el curso, el cálculo simultáneo de promedios ponderados, ordenación por notas y asignación de admitidos para miles de postulantes en PostgreSQL puede degradar el rendimiento del servidor web.
    *   *Mitigación:* Implementar procedimientos almacenados optimizados en PostgreSQL para realizar las asignaciones de cupos en bloque y utilizar cachés para los reportes estadísticos.
2.  **Riesgo de Integridad en Asignación de Cupos Concurrentes:** Que dos administradores corran el proceso de selección o reasignación en paralelo, provocando que se asignen más cupos de los permitidos para una carrera.
    *   *Mitigación:* Bloqueo transaccional estricto (Locks a nivel de tabla de carreras o transacciones serializables) durante la ejecución del proceso de adjudicación de cupos.
3.  **Seguridad y Alteración de Notas No Autorizada:** Acceso indebido a cuentas de docentes o manipulación directa de la base de datos para alterar promedios y beneficiar a postulantes específicos.
    *   *Mitigación:* Implementación de logs de auditoría inmutables en la base de datos (triggers) que registren quién, cuándo y qué valor anterior/nuevo modificó en las notas.

---

### 7. Alcance y Exclusiones

#### En Alcance (Dentro del Proyecto)
*   Portal de registro público para postulantes y back-office administrativo para docentes y personal de admisiones.
*   Algoritmos automatizados de agrupación, cálculo de notas ponderadas y adjudicación de cupos (1ra y 2da opción).
*   Publicación y consulta de resultados finales de admisión.
*   Reportes analíticos de rendimiento por docente, materia y grupo.

#### Exclusiones Explícitas (Fuera del Proyecto)
*   **Control de Asistencia:** No se registrará la asistencia diaria de los alumnos a las clases del CUP.
*   **Avance Diario de Clases:** No se controlará el avance del contenido programático ni el diario de clases de los docentes.
*   **Aplicación Móvil:** No se desarrollarán aplicaciones nativas (Android/iOS). El acceso móvil se garantiza únicamente a través de la web responsiva.
*   **Pasarela de Pagos:** No se integrará el cobro de aranceles de inscripción en línea. Los postulantes realizarán los pagos de manera presencial o mediante canales bancarios externos, y el Administrador registrará la confirmación del pago de forma manual o mediante importación masiva de un archivo de conciliación bancaria.

---

### 8. Glosario de Términos

*   **CUP (Curso Universitario de Preparación / Preuniversitario):** Periodo formativo y evaluativo previo al ingreso a las carreras universitarias de pregrado.
*   **Postulante:** Persona inscrita en el sistema que aspira a obtener una plaza en una carrera universitaria a través del CUP.
*   **Admitido:** Postulante que cumplió con la nota mínima aprobatoria y obtuvo una plaza dentro del cupo límite establecido para su carrera seleccionada.
*   **No Admitido:** Postulante que aprobó las materias pero su nota final no fue lo suficientemente alta para ingresar dentro de los cupos definidos para sus opciones de carrera.
*   **Reasignado:** Postulante que, habiendo reprobado en el acceso por cupo a su primera opción, es ubicado en los cupos sobrantes de su segunda opción seleccionada.
*   **Ponderación:** Porcentaje de peso relativo asignado a una evaluación (ej. Primer parcial 30%, Examen final 50%) para el cálculo de la nota final sobre 100 puntos.
*   **Cupo Remanente:** Plazas de admisión que no fueron cubiertas en la primera fase de selección de postulantes y quedan disponibles para postulantes de segunda opción o procesos extraordinarios.
