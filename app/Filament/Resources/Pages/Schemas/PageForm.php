<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class PageForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('release_id')
                ->relationship('release', 'title', modifyQueryUsing: function (Builder $q) {
                    if (auth()->user()?->hasRole('artist')) {
                        $q->whereHas('artist', fn ($aq) => $aq->where('user_id', auth()->id()));
                    }
                    return $q;
                })
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->maxLength(255)
                ->helperText('La stå tom for å generere automatisk.'),

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
        ])->columns(2);
    }
}