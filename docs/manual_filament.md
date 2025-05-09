# Manual Técnico de Filament - POSITIVOS

## Introducción

Este documento técnico describe la implementación y uso del panel administrativo Filament en el proyecto POSITIVOS. Filament es un kit de herramientas TALL stack (Tailwind, Alpine, Laravel, Livewire) que proporciona un panel de administración flexible y potente para aplicaciones Laravel.

## Tecnologías Utilizadas

- **Laravel** - Framework de desarrollo PHP
- **Filament** - Panel administrativo
- **Livewire** - Framework de interfaces dinámicas
- **Alpine.js** - Framework JavaScript para interactividad
- **Tailwind CSS** - Framework CSS utility-first

## Estructura del Panel Administrativo

### Configuración Base

El panel administrativo está personalizado con el nombre "POSITIVOS" y utiliza un esquema de colores verde corporativo. La ruta de acceso al panel es `/positivo`.

```php
// Configuración básica en app/Providers/Filament/AdminPanelProvider.php
return $panel
    ->default()
    ->id('admin')
    ->path('positivo')
    ->login()
    ->colors([
        'primary' => Color::Green,
    ])
    ->brandName('POSITIVOS')
    ->brandLogo(asset('images/logo-positivos.png'))
    ->favicon(asset('images/favicon.ico'));
```

### Recursos Principales

El panel se organiza en los siguientes recursos CRUD:

1. **ActividadResource** - Gestión de registros de actividades de horas extras
2. **JefeInmediatoResource** - Administración de jefes inmediatos
3. **TorreResource** - Gestión de torres operativas
4. **TipoCasoResource** - Administración de los tipos de casos
5. **UserResource** - Gestión de usuarios del sistema
6. **RoleResource** - Administración de roles
7. **PermissionResource** - Administración de permisos individuales

## Representación Visual de la Arquitectura

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│                   PANEL POSITIVOS                       │
│                                                         │
├────────────┬────────────┬────────────┬─────────────────┤
│            │            │            │                 │
│ Actividades│  Torres    │  Tipos de  │ Administración  │
│            │            │   Caso     │   de Accesos    │
│            │            │            │                 │
├────────────┴────────────┴────────────┼─────────────────┤
│                                      │                 │
│           Jefes Inmediatos           │    Usuarios     │
│                                      │                 │
├──────────────────────────────────────┼─────────────────┤
│                                      │                 │
│            Sistema de                │     Roles       │
│         Aprobación de Horas          │                 │
│                                      │                 │
└──────────────────────────────────────┴─────────────────┘
```

## Navegación y Organización

La navegación del panel se organiza en grupos temáticos para facilitar la gestión:

```php
protected function getNavigationGroups(): array
{
    return [
        NavigationGroup::make()
            ->label('Gestión Operativa')
            ->items([
                NavigationItem::make('Actividades'),
                NavigationItem::make('Torres'),
                NavigationItem::make('Tipos de Caso'),
            ]),
        NavigationGroup::make()
            ->label('Recursos Humanos')
            ->items([
                NavigationItem::make('Jefes Inmediatos'),
            ]),
        NavigationGroup::make()
            ->label('Administración de Accesos')
            ->items([
                NavigationItem::make('Usuarios'),
                NavigationItem::make('Roles'),
                NavigationItem::make('Permisos'),
            ]),
    ];
}
```

## Recursos y Formularios

### Ejemplo: ActividadResource

```php
// Estructura básica del formulario de actividades
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('Información Personal')
                ->schema([
                    TextInput::make('documento')
                        ->required(),
                    TextInput::make('nombre')
                        ->required(),
                    Select::make('jefe_inmediato_id')
                        ->relationship('jefeInmediato', 'nombre')
                        ->required(),
                ]),
                
            Section::make('Detalles del Caso')
                ->schema([
                    Select::make('torre_id')
                        ->relationship('torre', 'nombre')
                        ->required(),
                    Select::make('tipo_caso_id')
                        ->relationship('tipoCaso', 'nombre')
                        ->required(),
                    Textarea::make('justificacion')
                        ->required(),
                ]),
                
            Section::make('Fecha y Horas')
                ->schema([
                    DatePicker::make('fecha')
                        ->required(),
                    TimePicker::make('hora_inicio')
                        ->required(),
                    TimePicker::make('hora_fin')
                        ->required(),
                ]),
        ]);
}
```

### Tablas de Recursos

```php
// Ejemplo de configuración de tabla para actividades
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('documento'),
            TextColumn::make('nombre')
                ->searchable(),
            TextColumn::make('jefeInmediato.nombre'),
            TextColumn::make('torre.nombre'),
            TextColumn::make('tipoCaso.nombre'),
            DateColumn::make('fecha'),
            TextColumn::make('estado')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pendiente' => 'warning',
                    'aprobada_coordinador' => 'info',
                    'rechazada_coordinador' => 'danger',
                    'aprobada_final' => 'success',
                    'rechazada_final' => 'danger',
                }),
        ])
        ->filters([
            SelectFilter::make('estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'aprobada_coordinador' => 'Aprobada por Coordinador',
                    'rechazada_coordinador' => 'Rechazada por Coordinador',
                    'aprobada_final' => 'Aprobada Final',
                    'rechazada_final' => 'Rechazada Final',
                ]),
            SelectFilter::make('torre_id')
                ->relationship('torre', 'nombre'),
        ])
        ->actions([
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('aprobar')
                    ->visible(fn (Model $record): bool => 
                        $record->estado === 'pendiente' && auth()->user()->hasPermissionTo('aprobar actividades'))
                    ->action(function (Model $record) {
                        // Lógica de aprobación
                    }),
            ]),
        ]);
}
```

## Sistema de Aprobación de Horas Extras

El sistema de aprobación se implementa mediante acciones personalizadas en Filament y un flujo de dos niveles:

### Flujo de Aprobación

1. **Coordinador**: Primera revisión de la solicitud
2. **Administrador**: Aprobación final

### Estados del Flujo

- `pendiente` - Estado inicial de toda solicitud
- `aprobada_coordinador` - Aprobada en primera revisión
- `rechazada_coordinador` - Rechazada en primera revisión
- `aprobada_final` - Aprobada por administrador
- `rechazada_final` - Rechazada por administrador

### Implementación de Acciones

```php
// Ejemplo de acción de aprobación
Action::make('aprobarCoordinador')
    ->requiresConfirmation()
    ->form([
        Textarea::make('comentarios')
            ->label('Comentarios (opcional)'),
    ])
    ->action(function (array $data, Model $record): void {
        app(AprobacionHorasService::class)->aprobarCoordinador(
            $record,
            auth()->user(),
            $data['comentarios'] ?? null
        );
        
        Notification::make()
            ->title('Solicitud aprobada correctamente')
            ->success()
            ->send();
    });
```

## Integración de Roles y Permisos

El sistema utiliza Spatie Laravel Permission integrado con Filament para gestionar el acceso al panel y funcionalidades.

### Roles Predefinidos

1. **Super Admin**: Acceso completo al sistema
2. **Coordinador**: Gestión operativa y primera aprobación
3. **Administrador**: Aprobación final y gestión de configuraciones
4. **Operador**: Solo registro de actividades

### Implementación en Filament

```php
// Configuración de acceso al panel
public static function canAccessPanel(Panel $panel): bool
{
    return auth()->check() && (
        auth()->user()->hasAnyRole(['Super Admin', 'Coordinador', 'Administrador', 'Operador']) ||
        auth()->user()->hasAnyPermission([
            'ver actividades',
            'crear actividades',
            'editar actividades',
            'eliminar actividades',
            // Otros permisos...
        ])
    );
}

// Control de acceso a recursos específicos
public static function canViewAny(): bool
{
    return auth()->user()->can('ver actividades');
}

public static function canCreate(): bool
{
    return auth()->user()->can('crear actividades');
}
```

## Personalización Estética

### Tema Personalizado

```php
// Colores personalizados en el panel
->colors([
    'primary' => Color::hex('#00796B'),
    'danger' => Color::Rose,
    'gray' => Color::Slate,
    'info' => Color::Blue,
    'success' => Color::Emerald,
    'warning' => Color::Orange,
])
```

### Logos y Recursos Visuales

Los assets visuales se almacenan en la carpeta `public/images/`:
- `logo-positivos.png` - Logo principal del panel
- `favicon.ico` - Ícono del sitio
- `default-avatar.png` - Avatar por defecto para usuarios

## Buenas Prácticas de Desarrollo

1. **Separación de responsabilidades**: Usar Services para lógica de negocio compleja
2. **Políticas de acceso**: Implementar políticas de Laravel para control granular
3. **Namespace organizado**: Mantener las clases de Filament bajo `App\Filament`
4. **Recursos agrupados**: Agrupar recursos relacionados en subdirectorios
5. **Pruebas**: Crear pruebas específicas para cada recurso y acción personalizada

```php
// Ejemplo de servicio para encapsular lógica
class AprobacionHorasService
{
    public function aprobarCoordinador(Actividad $actividad, User $user, ?string $comentarios = null): void
    {
        $actividad->update([
            'estado' => 'aprobada_coordinador',
            'comentarios' => $comentarios,
            'aprobador_id' => $user->id,
            'fecha_aprobacion' => now(),
        ]);
        
        // Enviar notificación
        if ($actividad->email_notificacion) {
            Mail::to($actividad->email_notificacion)
                ->send(new AprobacionHorasNotificacion($actividad));
        }
    }
}
```

## Resolución de Problemas Comunes

### Acceso Denegado

- Verificar roles y permisos del usuario
- Comprobar implementación del método `canAccessPanel()`
- Revisar si el usuario está autenticado correctamente

### Personalización de Vistas

- Las vistas personalizadas deben ubicarse en `resources/views/filament/`
- Publicar assets para sobrescribir: `php artisan filament:assets`

### Performance

- Evitar consultas N+1 usando `->preload()` en relaciones
- Implementar caché para datos frecuentemente accedidos
- Usar Jobs para procesos pesados como envío masivo de notificaciones

## Comandos Artisan para Filament

Para mantener la estructura coherente en el proyecto, utiliza estos comandos para crear componentes Filament:

### Recursos

```bash
# Crear un recurso básico (CRUD completo)
php artisan make:filament-resource Actividad

# Crear un recurso con generador de políticas
php artisan make:filament-resource JefeInmediato --generate-policy

# Recurso con soft deletes
php artisan make:filament-resource TipoCaso --soft-deletes

# Recurso con vistas personalizadas
php artisan make:filament-resource Torre --view

# Recurso simple (sin CRUD completo)
php artisan make:filament-resource Usuario --simple
```

### Relaciones

```bash
# Crear un gestor de relaciones
php artisan make:filament-relation-manager ActividadResource JefeInmediato jefe_inmediato_id
```

### Páginas

```bash
# Crear página independiente
php artisan make:filament-page Dashboard

# Página asociada a un recurso
php artisan make:filament-page AprobacionMasiva --resource=ActividadResource --type=custom
```

### Widgets

```bash
# Widget para estadísticas
php artisan make:filament-widget ActividadesStats

# Widget de tabla
php artisan make:filament-widget UltimasActividades --table

# Widget de gráficos
php artisan make:filament-widget MetricsOverview --chart
```

### Formularios

```bash
# Componente de formulario personalizado
php artisan make:filament-form-field JustificacionConAdjunto
```

### Temas y Estilos

```bash
# Crear un tema personalizado
php artisan make:filament-theme positivos-theme

# Publicar assets de Filament para personalización
php artisan filament:assets
```

### Autenticación y Acceso

```bash
# Customizar el panel de autenticación
php artisan vendor:publish --tag=filament-panels-auth-views

# Publicar las migraciones de spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Mantenimiento

```bash
# Limpiar caché de Filament
php artisan filament:cache-reset

# Actualizar alias de Filament
php artisan filament:upgrade
```

### Estructura de Directorios

Mantener esta estructura para organizar los componentes:

```
app/
  └── Filament/
      ├── Resources/           # Recursos CRUD
      │   ├── ActividadResource/
      │   │   ├── Pages/
      │   │   └── Widgets/
      │   └── ...
      ├── Pages/              # Páginas personalizadas
      ├── Widgets/            # Widgets globales
      └── Services/           # Servicios relacionados a Filament
```

## Referencias

- [Documentación oficial de Filament](https://filamentphp.com/docs)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Notifications](https://laravel.com/docs/10.x/notifications)
