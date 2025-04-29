# Sistema de Aprobación de Horas Extras - POSITIVOS

## Introducción

Este documento técnico describe la implementación del sistema de aprobación de horas extras en la aplicación POSITIVOS. El sistema permite a los coordinadores y administradores revisar, aprobar o rechazar las solicitudes de horas extras de los empleados, con notificaciones automáticas por correo electrónico.

## Tecnologías Utilizadas

- **Laravel** - Framework PHP backend
- **Filament** - Panel administrativo
- **Livewire** - Componentes interactivos
- **Spatie Laravel Permission** - Gestión de roles y permisos
- **Laravel Notifications** - Sistema de notificaciones

## Arquitectura del Sistema

El sistema de aprobación de horas extras está diseñado siguiendo una arquitectura por capas:

1. **Capa de Datos**: Modelo Actividad y migraciones
2. **Capa de Servicios**: AprobacionHorasService
3. **Capa de Notificaciones**: HoraExtraAprobada y HoraExtraRechazada
4. **Capa de Presentación**: Filament Resources y Livewire Components

### Diagrama de Flujo de Aprobación

```
┌─────────────┐     ┌───────────────┐     ┌────────────────┐
│  Empleado   │────►│  Coordinador  │────►│ Administrador  │
│ (Solicitante)│     │   (1ª Revisión) │     │  (Revisión Final)│
└─────────────┘     └───────────────┘     └────────────────┘
       │                    │                     │
       ▼                    ▼                     ▼
┌─────────────┐     ┌───────────────┐     ┌────────────────┐
│  Pendiente  │────►│ Aprobada/     │────►│   Aprobada/    │
│             │     │ Rechazada     │     │   Rechazada    │
│             │     │ Coordinador   │     │   Final        │
└─────────────┘     └───────────────┘     └────────────────┘
                            │                     │
                            └─────────┬───────────┘
                                      ▼
                            ┌─────────────────────┐
                            │  Notificación por   │
                            │  Correo Electrónico │
                            └─────────────────────┘
```

## Modelo de Datos

### Nuevos campos en la tabla `actividads`

| Campo               | Tipo                | Descripción                                         |
|---------------------|---------------------|-----------------------------------------------------|
| email_notificacion  | string              | Email donde se envían las notificaciones            |
| estado              | enum                | Estados: pendiente, aprobada_coordinador, rechazada_coordinador, aprobada_final, rechazada_final |
| comentarios         | text                | Comentarios sobre la aprobación o rechazo           |
| aprobador_id        | unsignedBigInteger  | ID del usuario que aprobó/rechazó                   |
| fecha_aprobacion    | timestamp           | Fecha y hora de la aprobación o rechazo             |

### Estados posibles de una hora extra

| Estado                    | Descripción                            | Color UI  |
|---------------------------|----------------------------------------|-----------|
| pendiente                 | Recién creada, en espera de revisión   | Amarillo  |
| aprobada_coordinador      | Aprobada por un coordinador            | Azul      |
| rechazada_coordinador     | Rechazada por un coordinador           | Rojo      |
| aprobada_final            | Aprobación final por un administrador  | Verde     |
| rechazada_final           | Rechazo final por un administrador     | Rojo      |

## Flujo de Trabajo Técnico

### 1. Registro de Solicitud

**Componente**: `ExtraHoursForm` (Livewire)

**Proceso**:
1. El empleado completa el formulario de 3 pasos (información personal, detalles del caso, fecha y horas)
2. Al enviar el formulario, se crea un nuevo registro en la tabla `actividads`
3. El campo `estado` se establece automáticamente como "pendiente"
4. Se almacena el email de notificación proporcionado por el empleado
5. Se envía una notificación por correo electrónico al solicitante confirmando el registro

**Código relevante** (método save en `ExtraHoursForm.php`):
```php
$actividad = Actividad::create([
    'documento_identidad' => $this->documento_identidad,
    'nombre_completo' => $this->nombre_completo,
    'email_notificacion' => $this->email_notificacion,
    'torre_id' => $this->torre_id,
    // ... otros campos ...
    'estado' => Actividad::ESTADO_PENDIENTE,
]);

// Enviar notificación por correo electrónico
if ($this->email_notificacion) {
    try {
        $notifiable = new AnonymousNotifiable;
        $notifiable->route('mail', $this->email_notificacion);
        $notifiable->notify(new HoraExtraRegistrada($actividad));
        
        Log::info('Notificación enviada a: ' . $this->email_notificacion);
    } catch (\Exception $e) {
        Log::error('Error al enviar la notificación: ' . $e->getMessage());
    }
}
```

### 2. Aprobación/Rechazo por Coordinador

**Servicio**: `AprobacionHorasService`

**Métodos**:
- `aprobarPorCoordinador(Actividad $actividad, ?string $comentarios = null): bool`
- `rechazarPorCoordinador(Actividad $actividad, string $comentarios): bool`

**Permisos requeridos**:
- "aprobar horas extras"
- "rechazar horas extras"

**Proceso**:
1. El coordinador revisa la solicitud en el panel administrativo
2. Puede aprobar la solicitud (estado cambia a "aprobada_coordinador")
3. O rechazar la solicitud (estado cambia a "rechazada_coordinador")
4. Se registra el ID del coordinador y la fecha de acción
5. Se envía una notificación por email al solicitante

### 3. Aprobación/Rechazo Final por Administrador

**Servicio**: `AprobacionHorasService`

**Métodos**:
- `aprobarFinal(Actividad $actividad, ?string $comentarios = null): bool`
- `rechazarFinal(Actividad $actividad, string $comentarios): bool`

**Permisos requeridos**:
- "aprobar horas extras"
- "rechazar horas extras"
- Rol "Administrador"

**Proceso**:
1. El administrador revisa la solicitud (sea pendiente o ya aprobada por coordinador)
2. Puede aprobar definitivamente (estado cambia a "aprobada_final") 
3. O rechazar definitivamente (estado cambia a "rechazada_final")
4. Se registra el ID del administrador y la fecha de acción
5. Se envía una notificación por email al solicitante

## Sistema de Notificaciones

El sistema utiliza Laravel Notifications para enviar correos electrónicos a los empleados cuando su solicitud es aprobada o rechazada.

### Clases de Notificación

1. **HoraExtraRegistrada**
   - Enviada inmediatamente cuando se registra una nueva solicitud de hora extra
   - Incluye detalles de la hora extra registrada (ID, fecha, horario)
   - Informa al solicitante que su solicitud está pendiente de revisión

2. **HoraExtraAprobada**
   - Enviada cuando una solicitud es aprobada (por coordinador o final)
   - Incluye detalles de la hora extra y cualquier comentario
   - Personaliza el mensaje según nivel de aprobación (coordinador/final)

3. **HoraExtraRechazada**
   - Enviada cuando una solicitud es rechazada (por coordinador o final)
   - Incluye motivo del rechazo y detalles de la solicitud
   - Personaliza el mensaje según nivel de rechazo (coordinador/final)

### Implementación Técnica

Para permitir el envío de correos a destinatarios que no son usuarios del sistema (empleados que registran horas extras), se implementó una clase auxiliar:

```php
// AnonymousNotifiable: permite enviar notificaciones a destinatarios no registrados
class AnonymousNotifiable
{
    use Notifiable;

    protected $routes = [];

    public function route($channel, $route)
    {
        $this->routes[$channel] = $route;
        return $this;
    }

    public function routeNotificationFor($channel)
    {
        return $this->routes[$channel] ?? null;
    }
}
```

### Servicio de Aprobación

El servicio principal para gestionar las aprobaciones y envío de notificaciones:

```php
private function enviarNotificacionAprobacion(Actividad $actividad, string $nivel): void
{
    $notifiable = new AnonymousNotifiable();
    $notifiable->route('mail', $actividad->email_notificacion);
    
    Notification::send($notifiable, new HoraExtraAprobada($actividad, $nivel));
    
    // Notificación visual en la interfaz
    FilamentNotification::make()
        ->title('Correo enviado')
        ->body('Notificación de aprobación enviada a: ' . $actividad->email_notificacion)
        ->success()
        ->send();
}
```

### Extensión del Formulario de Edición

Para capturar cambios de estado realizados directamente desde el formulario de edición de Filament, se ha extendido la clase `EditActividad`:

```php
// Sobrescritura del método save para detectar cambios de estado
public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
{
    try {
        // Guardar estado actual antes de actualizar
        $estadoAnterior = $this->record->estado ?? Actividad::ESTADO_PENDIENTE;
        $emailNotificacion = $this->record->email_notificacion;
        
        // Guardar normalmente
        parent::save($shouldRedirect, $shouldSendSavedNotification);
        
        // Refrescar registro
        $this->record->refresh();
        
        // Verificar cambio de estado
        $nuevoEstado = $this->record->estado;
        
        if ($nuevoEstado !== $estadoAnterior && $nuevoEstado !== Actividad::ESTADO_PENDIENTE) {
            // Actualizar aprobador y fecha si necesario
            // Enviar notificación correspondiente según tipo de cambio
            // Mostrar confirmación visual
        }
    } catch (\Exception $e) {
        Log::error('Error al procesar formulario: ' . $e->getMessage());
    }
}
```

## Interfaz de Usuario

### Panel Administrativo (Filament)

El recurso `ActividadResource` ha sido mejorado con:

1. **Columnas de Estado**
   - Estado de aprobación con badges de colores
   - Información del aprobador
   - Fecha de aprobación/rechazo

2. **Acciones Contextuales**
   - Botones para aprobar/rechazar visibles según rol y estado
   - Modales para ingresar comentarios
   - Notificaciones en UI tras acciones exitosas

3. **Filtros de Vista**
   - Filtros por estado para facilitar la gestión
   
4. **Confirmación Visual de Envío de Correos**
   - Notificaciones visibles cuando se envía un correo al solicitante
   - Muestra explícitamente la dirección de correo destino en la notificación
   - Funciona tanto para acciones específicas como para cambios de estado vía formulario

### Formulario Público

El formulario para registrar horas extras ahora incluye:
- Campo de email obligatorio para notificaciones
- Explicación del propósito del campo de email
- Validación adecuada del formato de email

## Seguridad y Validación

1. **Verificación de Permisos**
   - Cada acción verifica que el usuario tenga los permisos necesarios
   - Control basado en roles (Coordinador/Administrador)

2. **Validación de Estados**
   - Los métodos `puedeSerAprobadaPorCoordinador()` y `puedeSerAprobadaPorAdministrador()` validan el flujo correcto
   - Previenen acciones inválidas (ej. aprobar solicitudes ya rechazadas)

3. **Auditoría**
   - Cada cambio de estado registra quién y cuándo lo realizó
   - Los comentarios permiten justificar decisiones

## Configuración del Sistema

Para activar completamente el sistema de aprobación con notificaciones por correo, se deben seguir estos pasos:

1. **Ejecutar la migración para añadir los campos necesarios**:
```bash
sail artisan migrate
```

2. **Configurar el envío de correos en el archivo .env**:
```
MAIL_MAILER=smtp
MAIL_HOST=tu-servidor-smtp
MAIL_PORT=587
MAIL_USERNAME=tu-usuario
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@tudominio.com
MAIL_FROM_NAME="POSITIVOS - Claro Data Center"
```

3. **Asignar permisos a usuarios**:
Los usuarios necesitan los permisos "aprobar horas extras" y "rechazar horas extras" según el esquema de roles definido en el sistema.

## Extensiones Futuras

El sistema está diseñado para permitir las siguientes mejoras:

1. **Métricas y Reportes**
   - Añadir tableros para visualizar tiempos de aprobación
   - Generar reportes de horas extras por departamento/torre

2. **Integración con Sistemas de Nómina**
   - Exportar horas extras aprobadas a sistemas de nómina
   - Calcular automáticamente valores a pagar

3. **Flujos de Trabajo Personalizados**
   - Permitir pasos adicionales de aprobación para casos especiales
   - Implementar recordatorios para solicitudes pendientes

4. **Notificaciones Adicionales**
   - Notificaciones push o SMS
   - Recordatorios para aprobadores

---

Documento creado el: 28/04/2025  
Última actualización: 28/04/2025
