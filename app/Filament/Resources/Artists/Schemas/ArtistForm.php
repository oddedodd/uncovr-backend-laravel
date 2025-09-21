<?php

namespace App\Filament\Resources\Artists\Schemas;

use App\Models\Artist;
use Filament\Schemas\Schema;
use Filament\Forms;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(Artist::class) // 👈 Viktig i v4
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->helperText('Leave blank to generate automatically from name.')
                    ->maxLength(255),

                Forms\Components\Textarea::make('bio')
                    ->rows(3),

                Forms\Components\TextInput::make('links')
                    ->label('Links (JSON or string)')
                    ->helperText('Optional'),
            ])
            ->columns(2);
    }
}