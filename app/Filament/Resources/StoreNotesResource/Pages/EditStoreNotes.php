<?php

namespace App\Filament\Resources\StoreNotesResource\Pages;

use App\Filament\Resources\StoreNotesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreNotes extends EditRecord
{
    protected static string $resource = StoreNotesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
