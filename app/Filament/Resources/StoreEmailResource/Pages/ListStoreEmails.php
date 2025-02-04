<?php

namespace App\Filament\Resources\StoreEmailResource\Pages;

use App\Filament\Resources\StoreEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreEmails extends ListRecords
{
    protected static string $resource = StoreEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
