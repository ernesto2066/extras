<?php

namespace App\Filament\Resources\JefeInmediatoResource\Pages;

use App\Filament\Resources\JefeInmediatoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJefeInmediato extends EditRecord
{
    protected static string $resource = JefeInmediatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
