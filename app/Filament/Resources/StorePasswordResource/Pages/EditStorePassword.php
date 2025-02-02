<?php

namespace App\Filament\Resources\StorePasswordResource\Pages;

use App\Filament\Resources\StorePasswordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorePassword extends EditRecord
{
    protected static string $resource = StorePasswordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
