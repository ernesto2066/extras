<?php

namespace App\Filament\Resources\TipoCasoResource\Pages;

use App\Filament\Resources\TipoCasoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoCasos extends ListRecords
{
    protected static string $resource = TipoCasoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
