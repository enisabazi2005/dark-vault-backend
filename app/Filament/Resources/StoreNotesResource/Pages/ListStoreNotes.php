<?php

namespace App\Filament\Resources\StoreNotesResource\Pages;

use App\Filament\Resources\StoreNotesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreNotes extends ListRecords
{
    protected static string $resource = StoreNotesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
