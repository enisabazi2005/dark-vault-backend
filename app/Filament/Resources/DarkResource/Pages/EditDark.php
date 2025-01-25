<?php

namespace App\Filament\Resources\DarkResource\Pages;

use App\Filament\Resources\DarkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDark extends EditRecord
{
    protected static string $resource = DarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
