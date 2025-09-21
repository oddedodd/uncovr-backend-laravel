<?php

namespace App\Filament\Resources\Artists\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Artist-felter
                Forms\Components\TextInput::make('name')
                    ->label('Artist Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('La stå tom for å generere automatisk.')
                    ->maxLength(255),

                Forms\Components\Textarea::make('bio')
                    ->label('Biography')
                    ->rows(3),

                Forms\Components\TextInput::make('links')
                    ->label('Links (JSON / tekst)')
                    ->maxLength(500),

                // Eier (User) – synlig for admin/label, dehydreres slik at Page-klassen får verdiene
                Forms\Components\TextInput::make('owner_email')
                    ->label('Owner Email')
                    ->email()
                    ->required(fn () => auth()->user()?->hasAnyRole(['admin', 'label']))
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'label'])),

                Forms\Components\TextInput::make('owner_password')
                    ->label('Owner Password')
                    ->password()
                    ->dehydrated() // send videre til Page for hashing
                    ->required(fn ($record) => $record === null && auth()->user()?->hasAnyRole(['admin','label']))
                    ->helperText('Tomt ved redigering = uendret passord.')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'label'])),
            ])
            ->columns(2);
    }
}