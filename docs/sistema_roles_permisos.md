# Sistema de Roles y Permisos - POSITIVOS

## Introducción

Este documento describe el sistema de roles y permisos implementado en la aplicación de gestión de horas extras POSITIVOS. El sistema utiliza el paquete Spatie Laravel Permission para proporcionar un control de acceso basado en roles (RBAC) que permite una gestión granular de los permisos de usuario.

## Tecnologías Utilizadas

- **Laravel** - Framework de desarrollo
- **Filament** - Panel administrativo
- **Spatie Laravel Permission** - Gestión de roles y permisos

## Estructura de Roles

El sistema define tres roles principales con diferentes niveles de permisos:

### 1. Super Admin

**Descripción**: Acceso completo al sistema y todas sus funcionalidades.

**Responsabilidades**:
- Administración completa del sistema
- Gestión de usuarios, roles y permisos
- Acceso a todas las funcionalidades sin restricciones

### 2. Administrador

**Descripción**: Responsable de la gestión operativa diaria del sistema de horas extras.

**Responsabilidades**:
- Gestión completa de actividades y horas extras
- Aprobación/rechazo de solicitudes de horas extras
- Generación de informes y exportación de datos
- Administración de catálogos (jefes inmediatos, torres, tipos de caso)
- Visualización de todas las horas extras registradas

### 3. Coordinador

**Descripción**: Supervisor de primera línea que gestiona las solicitudes iniciales de horas extras.

**Responsabilidades**:
- Visualización de solicitudes de horas extras
- Aprobación/rechazo de horas extras
- Consulta de información de referencia (jefes, torres, tipos de caso)

## Permisos Detallados

Los permisos del sistema están agrupados por módulos funcionales:

### Módulo de Actividades (Horas Extras)

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver actividades | ✅ | ✅ | ✅ |
| crear actividades | ✅ | ✅ | ❌ |
| editar actividades | ✅ | ✅ | ❌ |
| eliminar actividades | ✅ | ✅ | ❌ |
| aprobar horas extras | ✅ | ✅ | ✅ |
| rechazar horas extras | ✅ | ✅ | ✅ |
| ver todas las horas extras | ✅ | ✅ | ✅ |
| exportar horas extras | ✅ | ✅ | ❌ |
| reportes horas extras | ✅ | ✅ | ❌ |

### Módulo de Jefes Inmediatos

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver jefes | ✅ | ✅ | ✅ |
| crear jefes | ✅ | ✅ | ❌ |
| editar jefes | ✅ | ✅ | ❌ |
| eliminar jefes | ✅ | ❌ | ❌ |

### Módulo de Torres

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver torres | ✅ | ✅ | ✅ |
| crear torres | ✅ | ❌ | ❌ |
| editar torres | ✅ | ❌ | ❌ |
| eliminar torres | ✅ | ❌ | ❌ |

### Módulo de Tipos de Caso

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver tipos caso | ✅ | ✅ | ✅ |
| crear tipos caso | ✅ | ❌ | ❌ |
| editar tipos caso | ✅ | ❌ | ❌ |
| eliminar tipos caso | ✅ | ❌ | ❌ |

### Módulo de Usuarios

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver usuarios | ✅ | ❌ | ❌ |
| crear usuarios | ✅ | ❌ | ❌ |
| editar usuarios | ✅ | ❌ | ❌ |
| eliminar usuarios | ✅ | ❌ | ❌ |

### Módulo de Roles y Permisos

| Permiso | Super Admin | Administrador | Coordinador |
|---------|:-----------:|:-------------:|:-----------:|
| ver roles | ✅ | ❌ | ❌ |
| crear roles | ✅ | ❌ | ❌ |
| editar roles | ✅ | ❌ | ❌ |
| eliminar roles | ✅ | ❌ | ❌ |
| ver permisos | ✅ | ❌ | ❌ |
| editar permisos | ✅ | ❌ | ❌ |

## Flujo de Trabajo para Horas Extras

### 1. Registro de Horas Extras

**Actor**: Empleado (a través de la interfaz pública)

**Proceso**:
1. El empleado accede al formulario público de registro de horas extras
2. Completa los datos requeridos en el formulario de 3 pasos:
   - Información Personal
   - Detalles del Caso
   - Fecha y Horas
3. El sistema registra la hora extra en estado "Pendiente"
4. Se muestra un mensaje de confirmación con el número de registro

### 2. Revisión y Aprobación Inicial

**Actor**: Coordinador

**Proceso**:
1. El Coordinador accede al panel administrativo (/positivo)
2. Navega a la sección de Actividades
3. Filtra las horas extras pendientes de revisión
4. Revisa los detalles de cada solicitud
5. Decide aprobar o rechazar cada solicitud
6. Registra comentarios o justificaciones si es necesario
7. El sistema cambia el estado de la solicitud a "Aprobada por Coordinador" o "Rechazada"

### 3. Validación Final

**Actor**: Administrador

**Proceso**:
1. El Administrador accede al panel administrativo
2. Revisa las horas extras en estado "Aprobada por Coordinador"
3. Valida la información y la conformidad con las políticas
4. Aprueba definitivamente la hora extra o la rechaza
5. El sistema actualiza el estado a "Aprobada" o "Rechazada"

### 4. Generación de Reportes

**Actor**: Administrador o Super Admin

**Proceso**:
1. El Administrador o Super Admin accede al panel administrativo
2. Navega a la sección de Reportes
3. Selecciona los criterios para el reporte (período, torre, tipo de caso, etc.)
4. Genera el reporte en el formato deseado (PDF, Excel, etc.)
5. Descarga o comparte el reporte según sea necesario

## Acceso al Panel Administrativo

El acceso al panel administrativo en la ruta `/positivo` está restringido a usuarios autenticados que:

1. Tengan la dirección de correo electrónico específica del administrador (`nomina@positivosmais.com`), o
2. Tengan asignado el rol de "Super Admin"

La autenticación se valida mediante el método `canAccessPanel` en el modelo User:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->email === 'nomina@positivosmais.com' || $this->hasRole('Super Admin');
}
```

## Comandos para Gestionar Roles y Permisos

Para gestionar roles y permisos desde la línea de comandos, se pueden utilizar los siguientes comandos:

```bash
# Publicar migraciones de Spatie Permission
sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Ejecutar migraciones
sail artisan migrate

# Cargar permisos y roles predefinidos
sail artisan db:seed --class=PermissionSeeder

# Limpiar caché de permisos
sail artisan cache:clear
```

## Verificación de Permisos en Código

Para verificar permisos en el código, se pueden utilizar los siguientes métodos:

```php
// Verificar si el usuario tiene un permiso específico
if ($user->can('ver actividades')) {
    // El usuario puede ver actividades
}

// Verificar si el usuario tiene un rol específico
if ($user->hasRole('Administrador')) {
    // El usuario es un Administrador
}

// Verificar múltiples permisos (requiere todos)
if ($user->hasAllPermissions(['ver actividades', 'aprobar horas extras'])) {
    // El usuario tiene todos los permisos necesarios
}

// Verificar múltiples permisos (requiere cualquiera)
if ($user->hasAnyPermission(['ver actividades', 'aprobar horas extras'])) {
    // El usuario tiene al menos uno de los permisos
}
```

## Consideraciones de Seguridad

- Todos los permisos deben verificarse tanto en el frontend como en el backend
- Las acciones críticas (aprobar/rechazar horas extras, eliminar registros) deben registrarse en un log de auditoría
- Los cambios en roles y permisos deben realizarse con precaución y por usuarios autorizados
- Se recomienda revisar periódicamente los usuarios con roles de alto privilegio (Super Admin, Administrador)

---

Documento creado el: 28/04/2025  
Última actualización: 28/04/2025
