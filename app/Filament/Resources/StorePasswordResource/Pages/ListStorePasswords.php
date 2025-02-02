<?php

namespace App\Filament\Resources\StorePasswordResource\Pages;

use App\Filament\Resources\StorePasswordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStorePasswords extends ListRecords
{
    protected static string $resource = StorePasswordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
