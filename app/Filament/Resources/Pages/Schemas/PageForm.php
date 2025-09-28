<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Blocks\PageBlocks;
use App\Models\Release;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Hvilken release siden tilhører
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

            TextInput::make('title')
                ->label('Page title')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label('Slug')
                ->helperText('La stå tom for å generere automatisk.')
                ->maxLength(255),

            // Page-nivå bakgrunn (arves ned til blokker uten egen farge)
            ColorPicker::make('background_color')
                ->label('Page background')
                ->nullable()
                ->helperText('Arves av blokker som ikke har egen bakgrunn.'),

            // Blocks (hentes fra egne blokklasse-filer)
            Builder::make('blocks')
                ->label('Blocks')
                ->blocks(PageBlocks::all())
                ->collapsible()
                ->reorderable()
                ->columnSpanFull(),
        ])->columns(2);
    }
}