<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorePrivateInfoResource\Pages;
use App\Filament\Resources\StorePrivateInfoResource\RelationManagers;
use App\Models\StorePrivateInfo;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StorePrivateInfoResource extends Resource
{
    protected static ?string $model = StorePrivateInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->label('Id')
                    ->required(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('info-1')
                    ->label('Information-1')
                    ->required(),
                TextInput::make('info-2')
                    ->label('Information-2')
                    ->required(),
                TextInput::make('info-3')
                    ->label('Information-3')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorePrivateInfos::route('/'),
            'create' => Pages\CreateStorePrivateInfo::route('/create'),
            'edit' => Pages\EditStorePrivateInfo::route('/{record}/edit'),
        ];
    }
}
