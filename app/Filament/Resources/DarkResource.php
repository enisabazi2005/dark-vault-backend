<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DarkResource\Pages;
use App\Models\DarkUsers;
use Filament\Forms;
use Filament\Forms\Components\{Grid, Section, TextInput, Select, FileUpload, DateTimePicker};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use App\Filament\Widgets\BlogPostsChart;


class DarkResource extends Resource
{
    protected static ?string $model = DarkUsers::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        Grid::make(2) 
                            ->schema([
                                TextInput::make('name')
                                    ->label('First Name')
                                    ->required(),

                                TextInput::make('lastname')
                                    ->label('Last Name')
                                    ->required(),

                                TextInput::make('email')
                                    ->email()
                                    ->required(),

                                TextInput::make('password')
                                    ->password()
                                    ->label('Password')
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->required(),
                                TextInput::make('request_id')
                                    ->label('Write your required id')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Profile Details')
                    ->schema([
                        Grid::make(2) // Keeps it clean
                            ->schema([
                                Select::make('gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                    ])
                                    ->required(),

                                TextInput::make('age')
                                    ->numeric()
                                    ->required(),

                                FileUpload::make('picture')
                                    ->image()
                                    ->label('Profile Picture'),

                                DateTimePicker::make('birthdate') // Fixed field name
                                    ->label('Date of Birth')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('lastname')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('gender')->sortable(),
                Tables\Columns\TextColumn::make('age')->sortable(),
                Tables\Columns\ImageColumn::make('picture')->circular(),
                Tables\Columns\TextColumn::make('birthdate')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Joined On')->dateTime(),
            ])
            ->filters([])
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
        return [];
    }
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDarks::route('/'),
            'create' => Pages\CreateDark::route('/create'),
            'edit' => Pages\EditDark::route('/{record}/edit'),
        ];
    }
    
}
