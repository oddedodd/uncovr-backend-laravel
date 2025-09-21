<?php

namespace App\Filament\Resources\Artists\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class ArtistForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('bio')
                ->rows(3),

            Forms\Components\TextInput::make('links')
                ->label('Links (JSON or string)')
                ->helperText('Optional'),
        ])->columns(2);
    }
}