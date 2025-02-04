<?php

namespace App\Filament\Resources\StorePrivateInfoResource\Pages;

use App\Filament\Resources\StorePrivateInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorePrivateInfo extends EditRecord
{
    protected static string $resource = StorePrivateInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
