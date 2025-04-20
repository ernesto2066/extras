<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JefeInmediatoResource\Pages;
use App\Filament\Resources\JefeInmediatoResource\RelationManagers;
use App\Models\JefeInmediato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JefeInmediatoResource extends Resource
{
    protected static ?string $model = JefeInmediato::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Jefes Inmediatos';
    
    protected static ?string $modelLabel = 'Jefe Inmediato';
    
    protected static ?string $pluralModelLabel = 'Jefes Inmediatos';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nombre del jefe inmediato'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListJefeInmediatos::route('/'),
            'create' => Pages\CreateJefeInmediato::route('/create'),
            'edit' => Pages\EditJefeInmediato::route('/{record}/edit'),
        ];
    }
}
