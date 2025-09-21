<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(Page::class) // 👈 Viktig i v4
            ->schema([
                Forms\Components\Select::make('release_id')
                    ->relationship('release', 'title', modifyQueryUsing: function (Builder $q) {
                        if (auth()->user()?->hasRole('artist')) {
                            $q->whereHas('artist', fn ($aq) => $aq->where('user_id', auth()->id()));
                        }
                        // ingen return nødvendig
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->helperText('Leave blank to auto-generate.'),

                Forms\Components\TextInput::make('page_type')
                    ->default('generic')
                    ->maxLength(50),

                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->default(1),

                Forms\Components\Select::make('status')
                    ->options([
                        'draft'     => 'draft',
                        'published' => 'published',
                    ])
                    ->default('draft'),

                Forms\Components\KeyValue::make('meta')
                    ->keyLabel('key')
                    ->valueLabel('value')
                    ->columnSpan('full'),
            ])
            ->columns(2);
    }
}