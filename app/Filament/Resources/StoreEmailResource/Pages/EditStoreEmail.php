<?php

namespace App\Filament\Resources\StoreEmailResource\Pages;

use App\Filament\Resources\StoreEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreEmail extends EditRecord
{
    protected static string $resource = StoreEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
