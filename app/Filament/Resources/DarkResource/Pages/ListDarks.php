<?php

namespace App\Filament\Resources\DarkResource\Pages;

use App\Filament\Resources\DarkResource;
use App\Filament\Widgets\BlogPostsChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDarks extends ListRecords
{
    protected static string $resource = DarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getWidgets(): array
    {
        return [
            BlogPostsChart::class, // Add the widget to be displayed on this page
        ];
    }
    
}
