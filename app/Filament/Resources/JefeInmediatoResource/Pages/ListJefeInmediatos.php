<?php

namespace App\Filament\Resources\JefeInmediatoResource\Pages;

use App\Filament\Resources\JefeInmediatoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJefeInmediatos extends ListRecords
{
    protected static string $resource = JefeInmediatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
