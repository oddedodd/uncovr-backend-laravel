<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Models\Release;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Knytt til release (artist ser kun egne)
            Forms\Components\Select::make('release_id')
                ->label('Release')
                ->required()
                ->searchable()
                ->preload()
                ->options(function () {
                    $q = Release::query()->orderBy('title');

                    if (auth()->user()?->hasRole('artist')) {
                        $q->whereHas('artist', fn ($qq) => $qq->where('user_id', auth()->id()));
                    }

                    return $q->pluck('title', 'id')->all();
                })
                ->helperText(
                    auth()->user()?->hasRole('artist')
                        ? 'Du kan kun velge dine egne utgivelser.'
                        : 'Admin/label kan velge blant alle utgivelser.'
                ),

            Forms\Components\TextInput::make('title')
                ->label('Page title')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('La stå tom for å generere automatisk.')
                ->maxLength(255),

            // 👉 NYTT: Page-nivå bakgrunnsfarge
            Forms\Components\ColorPicker::make('background_color')
                ->label('Page background')
                ->nullable()
                ->helperText('Denne arves av blokker som ikke har egen bakgrunn.'),
        ])->columns(2);
    }
}