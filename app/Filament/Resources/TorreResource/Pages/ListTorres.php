<?php

namespace App\Filament\Resources\TorreResource\Pages;

use App\Filament\Resources\TorreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTorres extends ListRecords
{
    protected static string $resource = TorreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
