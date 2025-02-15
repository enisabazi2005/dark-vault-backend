<?php

namespace App\Filament\Resources\FriendRequestsResource\Pages;

use App\Filament\Resources\FriendRequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFriendRequests extends ListRecords
{
    protected static string $resource = FriendRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
