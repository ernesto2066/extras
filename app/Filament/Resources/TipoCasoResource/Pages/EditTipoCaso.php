<?php

namespace App\Filament\Resources\TipoCasoResource\Pages;

use App\Filament\Resources\TipoCasoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoCaso extends EditRecord
{
    protected static string $resource = TipoCasoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
