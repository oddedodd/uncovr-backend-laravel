<?php

namespace App\Filament\Resources\Releases\Schemas;

use App\Models\Release;
use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(Release::class) // 👈 Viktig i v4
            ->schema([
                Forms\Components\Select::make('artist_id')
                    ->relationship('artist', 'name', modifyQueryUsing: function (Builder $q) {
                        if (auth()->user()?->hasRole('artist')) {
                            $q->where('user_id', auth()->id());
                        }
                        // ingen return nødvendig i v4
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
            ])
            ->columns(2);
    }
}