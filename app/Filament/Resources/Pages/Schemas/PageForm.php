<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Models\Release;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Velg release – filtrert for artist, full liste for admin/label
                Forms\Components\Select::make('release_id')
                    ->label('Release')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $query = Release::query()
                            ->orderBy('title');

                        if (auth()->user()?->hasRole('artist')) {
                            $query->whereHas('artist', fn ($q) =>
                                $q->where('user_id', auth()->id())
                            );
                        }

                        return $query->pluck('title', 'id')->all();
                    })
                    ->helperText(auth()->user()?->hasRole('artist')
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

                Forms\Components\Textarea::make('content')
                    ->label('Content (JSON/tekst)')
                    ->rows(6)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}