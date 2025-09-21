<?php

namespace App\Filament\Resources\Releases\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class ReleaseForm
{
    public static function configure(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('artist_id')
                ->relationship('artist', 'name', modifyQueryUsing: function (Builder $q) {
                    if (auth()->user()?->hasRole('artist')) {
                        $q->where('user_id', auth()->id());
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

            Forms\Components\Select::make('type')
                ->options([
                    'single' => 'single',
                    'album'  => 'album',
                    'ep'     => 'ep',
                ])
                ->default('single')
                ->required(),

            Forms\Components\DatePicker::make('release_date'),

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