<?php

namespace App\Filament\Resources\TorreResource\Pages;

use App\Filament\Resources\TorreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTorre extends EditRecord
{
    protected static string $resource = TorreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
