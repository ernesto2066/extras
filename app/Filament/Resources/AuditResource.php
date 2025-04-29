<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditResource\Pages;
use App\Filament\Resources\AuditResource\RelationManagers;
use OwenIt\Auditing\Models\Audit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administración de Accesos';
    
    protected static ?int $navigationSort = 4;
    
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la Auditoría')
                    ->schema([
                        Forms\Components\TextInput::make('user_type')
                            ->label('Tipo de Usuario')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_id')
                            ->label('ID de Usuario')
                            ->disabled(),
                        Forms\Components\TextInput::make('event')
                            ->label('Evento')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_type')
                            ->label('Tipo de Modelo')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_id')
                            ->label('ID del Modelo')
                            ->disabled(),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Dirección IP')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_agent')
                            ->label('Agente de Usuario')
                            ->disabled(),
                        Forms\Components\Textarea::make('old_values')
                            ->label('Valores Antiguos')
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    }
                                }
                                return is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state;
                            })
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('new_values')
                            ->label('Valores Nuevos')
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    }
                                }
                                return is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state;
                            })
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Fecha y Hora')
                            ->disabled(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->label('Usuario')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'Sistema';
                        
                        $user = User::find($state);
                        if ($user) {
                            return $user->name;
                        }
                        
                        return 'Usuario #' . $state;
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                    ]),
                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Modelo')
                    ->options(function () {
                        $types = Audit::distinct('auditable_type')->pluck('auditable_type')->toArray();
                        $options = [];
                        
                        foreach ($types as $type) {
                            $options[$type] = class_basename($type);
                        }
                        
                        return $options;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListAudits::route('/'),
            'view' => Pages\ViewAudit::route('/{record}'),
        ];
    }
}
