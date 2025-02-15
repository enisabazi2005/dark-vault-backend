<?php

namespace App\Filament\Resources\FriendRequestsResource\Pages;

use App\Filament\Resources\FriendRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFriendRequests extends EditRecord
{
    protected static string $resource = FriendRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
