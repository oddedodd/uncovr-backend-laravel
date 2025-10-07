<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Blocks\PageBlocks;
use App\Models\Release;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

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

            // Posisjon (unik per release)
            TextInput::make('position')
                ->label('Position')
                ->numeric()
                ->minValue(1)
                ->default(function (callable $get) {
                    $releaseId = $get('release_id');
                    if (! $releaseId) {
                        return null;
                    }

                    $last = Page::where('release_id', $releaseId)->max('position') ?? 0;
                    return $last + 1;
                })
                ->rules(function (callable $get) {
                    $releaseId = $get('release_id');
                    // Ignorer nåværende record ved edit (Filament legger {record} i route)
                    $currentId = request()->route('record');

                    $rule = Rule::unique('pages', 'position')
                        ->where(fn ($q) => $q->where('release_id', $releaseId));

                    if ($currentId) {
                        $rule = $rule->ignore($currentId);
                    }

                    return ['integer', 'min:1', $rule];
                })
                ->helperText('Unik per release. Lavere tall vises først.'),

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