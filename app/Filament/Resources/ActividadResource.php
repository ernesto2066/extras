<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActividadResource\Pages;
use App\Filament\Resources\ActividadResource\RelationManagers;
use App\Models\Actividad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                        Forms\Components\DatePicker::make('fecha_ejecucion')
                            ->required(),
                        Forms\Components\TimePicker::make('hora_inicio')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('hora_fin')
                            ->required()
                            ->seconds(false),
                    ])
                    ->columns(3),
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
                Tables\Columns\TextColumn::make('numero_casos')
                    ->label('Número de Casos'),
                Tables\Columns\TextColumn::make('fecha_ejecucion')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('hora_fin')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
