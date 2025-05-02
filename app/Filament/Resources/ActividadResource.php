<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActividadResource\Pages;
use App\Models\Actividad;
use App\Services\AprobacionHorasService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ActividadResource extends Resource
{
    protected static ?string $model = Actividad::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationLabel = 'Actividades';
    
    protected static ?string $modelLabel = 'Actividad';
    
    protected static ?string $pluralModelLabel = 'Actividades';
    
    protected static ?int $navigationSort = 0; // Primero en la lista

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('documento_identidad')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Escriba su respuesta'),
                        Forms\Components\TextInput::make('nombre_completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Escriba su respuesta'),
                        Forms\Components\TextInput::make('email_notificacion')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ejemplo@correo.com'),
                        Forms\Components\Select::make('jefe_inmediato_id')
                            ->relationship('jefeInmediato', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Detalles del Caso')
                    ->schema([
                        Forms\Components\Select::make('torre_id')
                            ->relationship('torre', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('tipo_caso_id')
                            ->relationship('tipoCaso', 'descripcion')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('cliente')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Escriba su respuesta'),
                        Forms\Components\TextInput::make('numero_casos')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: 12345/67890')
                            ->helperText('Ingrese múltiples códigos separados por /'),
                        Forms\Components\Textarea::make('descripcion')
                            ->required()
                            ->rows(3)
                            ->placeholder('Escriba su respuesta'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Fecha y Horas')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\DatePicker::make('fecha_inicio')
                                        ->label('Fecha de Inicio')
                                        ->required()
                                        ->maxDate(now())
                                        ->helperText('No se pueden registrar horas extras para fechas futuras'),
                                    Forms\Components\TimePicker::make('hora_inicio')
                                        ->label('Hora de Inicio')
                                        ->required()
                                        ->seconds(false),
                                ])->columns(1),
                                Forms\Components\Group::make([
                                    Forms\Components\DatePicker::make('fecha_fin')
                                        ->label('Fecha de Fin')
                                        ->required()
                                        ->maxDate(now())
                                        ->helperText('No se pueden registrar horas extras para fechas futuras'),
                                    Forms\Components\TimePicker::make('hora_fin')
                                        ->label('Hora de Fin')
                                        ->required()
                                        ->seconds(false),
                                ])->columns(1),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Estado y Aprobación')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->options([
                                Actividad::ESTADO_PENDIENTE => 'Pendiente',
                                Actividad::ESTADO_APROBADA_COORDINADOR => 'Aprobada por Coordinador',
                                Actividad::ESTADO_RECHAZADA_COORDINADOR => 'Rechazada por Coordinador',
                                Actividad::ESTADO_APROBADA_FINAL => 'Aprobada Final',
                                Actividad::ESTADO_RECHAZADA_FINAL => 'Rechazada Final',
                            ])
                            ->default(Actividad::ESTADO_PENDIENTE)
                            ->disabled(fn (string $context): bool => $context === 'create')
                            ->required(),
                        Forms\Components\Textarea::make('comentarios')
                            ->placeholder('Comentarios sobre la aprobación o rechazo')
                            ->rows(3)
                            ->required(fn (callable $get): bool => $get('estado') !== Actividad::ESTADO_PENDIENTE)
                            ->requiredWith('estado')
                            ->helperText('Debe incluir un comentario al aprobar o rechazar las horas extras'),
                    ])
                    ->visibleOn('edit')
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documento_identidad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_fin')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duracion_horas')
                    ->label('Duración (Horas)')
                    ->state(function (Actividad $record): float {
                        return $record->calcularDuracionHoras();
                    })
                    ->formatStateUsing(fn (float $state): string => number_format($state, 2) . ' h')
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('tipo_hora_extra')
                    ->label('Tipo')
                    ->state(function (Actividad $record): string {
                        return $record->getTipoHoraExtra();
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Festiva' ? 'warning' : 'primary')
                    ->tooltip(function (Actividad $record): string {
                        return $record->esDiaFestivo() ? 'Festivo o fin de semana' : 'Día hábil';
                    }),
                Tables\Columns\TextColumn::make('email_notificacion')
                    ->searchable()
                    ->sortable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('jefeInmediato.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Jefe Inmediato'),
                Tables\Columns\TextColumn::make('torre.nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoCaso.descripcion')
                    ->searchable()
                    ->sortable()
                    ->label('Tipo de Caso'),
                Tables\Columns\TextColumn::make('cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('hora_fin')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => Actividad::getEstadoColor($state))
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        Actividad::ESTADO_PENDIENTE => 'Pendiente',
                        Actividad::ESTADO_APROBADA_COORDINADOR => 'Aprobada Coord.',
                        Actividad::ESTADO_RECHAZADA_COORDINADOR => 'Rechazada Coord.',
                        Actividad::ESTADO_APROBADA_FINAL => 'Aprobada Final',
                        Actividad::ESTADO_RECHAZADA_FINAL => 'Rechazada Final',
                        default => 'Desconocido',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('aprobador.name')
                    ->label('Aprobador')
                    ->placeholder('Sin aprobar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_aprobacion')
                    ->label('Fecha Aprobación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Pendiente'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(fn (Actividad $record): bool => Auth::user()->can('editar actividades')),
                
                // Acción para aprobar por Coordinador
                Tables\Actions\Action::make('aprobarCoordinador')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label('Aprobar (Coord.)')
                    ->modalHeading('Aprobar Hora Extra como Coordinador')
                    ->modalDescription('Esta acción indica que la hora extra ha sido revisada y aprobada por un coordinador.')
                    ->form([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Comentarios')
                            ->placeholder('Detalle el motivo de la aprobación')
                            ->required()
                            ->helperText('Debe incluir un comentario al aprobar las horas extras')
                            ->rows(3),
                        Forms\Components\Hidden::make('nivel')->default('coordinador'),
                    ])
                    ->action(function (Actividad $record, array $data, AprobacionHorasService $service): void {
                        $service->aprobarPorCoordinador($record, $data['comentarios'] ?? null);
                    })
                    ->successNotification(
                        notification: fn(Actividad $record): Filament\Notifications\Notification => Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Hora Extra Aprobada')
                            ->body('La hora extra ha sido aprobada correctamente por Coordinador. Se ha enviado una notificación a: ' . $record->email_notificacion)
                    )
                    ->visible(fn (Actividad $record): bool => 
                        $record->puedeSerAprobadaPorCoordinador() && 
                        Auth::user()->can('aprobar horas extras')),
                    
                // Acción para rechazar por Coordinador
                Tables\Actions\Action::make('rechazarCoordinador')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->label('Rechazar (Coord.)')
                    ->modalHeading('Rechazar Hora Extra como Coordinador')
                    ->modalDescription('Esta acción indica que la hora extra ha sido revisada y rechazada por un coordinador.')
                    ->form([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Motivo de Rechazo')
                            ->placeholder('Explique el motivo del rechazo')
                            ->required()
                            ->rows(3),
                        Forms\Components\Hidden::make('nivel')->default('coordinador'),
                    ])
                    ->action(function (Actividad $record, array $data, AprobacionHorasService $service): void {
                        $service->rechazarPorCoordinador($record, $data['comentarios']);
                    })
                    ->successNotification(
                        notification: fn(Actividad $record): Filament\Notifications\Notification => Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Hora Extra Rechazada')
                            ->body('La hora extra ha sido rechazada por Coordinador. Se ha enviado una notificación a: ' . $record->email_notificacion)
                    )
                    ->visible(fn (Actividad $record): bool => 
                        $record->puedeSerAprobadaPorCoordinador() && 
                        Auth::user()->can('rechazar horas extras')),
                    
                // Acción para aprobar final (Administrador)
                Tables\Actions\Action::make('aprobarFinal')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->label('Aprobar Final')
                    ->modalHeading('Aprobar Hora Extra (Final)')
                    ->modalDescription('Esta acción indica que la hora extra ha sido revisada y aprobada definitivamente.')
                    ->form([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Comentarios')
                            ->placeholder('Detalle el motivo de la aprobación final')
                            ->required()
                            ->helperText('Debe incluir un comentario al aprobar las horas extras')
                            ->rows(3),
                        Forms\Components\Hidden::make('nivel')->default('final'),
                    ])
                    ->action(function (Actividad $record, array $data, AprobacionHorasService $service): void {
                        $service->aprobarFinal($record, $data['comentarios'] ?? null);
                    })
                    ->successNotification(
                        notification: fn(Actividad $record): Filament\Notifications\Notification => Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Hora Extra Aprobada')
                            ->body('La hora extra ha sido aprobada definitivamente. Se ha enviado una notificación a: ' . $record->email_notificacion)
                    )
                    ->visible(fn (Actividad $record): bool => 
                        $record->puedeSerAprobadaPorAdministrador() && 
                        Auth::user()->hasRole('Administrador')),
                    
                // Acción para rechazar final (Administrador)
                Tables\Actions\Action::make('rechazarFinal')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->label('Rechazar Final')
                    ->modalHeading('Rechazar Hora Extra (Final)')
                    ->modalDescription('Esta acción indica que la hora extra ha sido revisada y rechazada definitivamente.')
                    ->form([
                        Forms\Components\Textarea::make('comentarios')
                            ->label('Motivo de Rechazo')
                            ->placeholder('Explique el motivo del rechazo')
                            ->required()
                            ->rows(3),
                        Forms\Components\Hidden::make('nivel')->default('final'),
                    ])
                    ->action(function (Actividad $record, array $data, AprobacionHorasService $service): void {
                        $service->rechazarFinal($record, $data['comentarios']);
                    })
                    ->successNotification(
                        notification: fn(Actividad $record): Filament\Notifications\Notification => Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Hora Extra Rechazada')
                            ->body('La hora extra ha sido rechazada definitivamente. Se ha enviado una notificación a: ' . $record->email_notificacion)
                    )
                    ->visible(fn (Actividad $record): bool => 
                        $record->puedeSerAprobadaPorAdministrador() && 
                        Auth::user()->hasRole('Administrador')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActividads::route('/'),
            'create' => Pages\CreateActividad::route('/create'),
            'edit' => Pages\EditActividad::route('/{record}/edit'),
        ];
    }
}
