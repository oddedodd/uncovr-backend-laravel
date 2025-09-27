<?php

namespace App\Filament\Resources\Artists\Schemas;

use App\Models\Label;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // --- Artist (grunnfelt) ---
                Forms\Components\TextInput::make('name')
                    ->label('Artist Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        // Sett slug automatisk hvis det er tomt
                        if (blank($get('slug')) && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('La stå tom for å generere automatisk.')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('bio')
                    ->label('Biography')
                    ->rows(3)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('links')
                    ->label('Links (JSON / tekst)')
                    ->maxLength(500)
                    ->columnSpan(2),

                // --- Tilknytning (Label) ---
                Forms\Components\Select::make('label_id')
                    ->label('Label')
                    ->relationship('label', 'name')
                    ->searchable()
                    ->preload()
                    // Admin kan velge fritt
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                    // For label-rolle: lås til egen label (og skjul feltet for label-bruker)
                    ->default(function () {
                        if (auth()->check() && auth()->user()->hasRole('label')) {
                            return Label::where('owner_user_id', auth()->id())->value('id');
                        }
                        return null;
                    })
                    ->disabled(fn () => auth()->user()?->hasRole('label') ?? false)
                    ->columnSpan(2),

                // --- Eier (bruker) ---
                Forms\Components\TextInput::make('owner_email')
                    ->label('Owner Email')
                    ->email()
                    ->required(fn () => auth()->user()?->hasAnyRole(['admin', 'label']))
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'label'])),

                Forms\Components\TextInput::make('owner_password')
                    ->label('Owner Password')
                    ->password()
                    ->revealable()
                    ->dehydrated()
                    ->required(fn ($record) => $record === null && (auth()->user()?->hasAnyRole(['admin', 'label']) ?? false))
                    ->helperText('La stå tom ved redigering for å beholde eksisterende passord.')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'label'])),
            ])
            ->columns(2);
    }
}