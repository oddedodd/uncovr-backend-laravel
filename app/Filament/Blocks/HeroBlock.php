<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

class HeroBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('hero')
            ->label('Hero')
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(120),
                TextInput::make('subtitle')
                    ->label('Subtitle')
                    ->maxLength(200),
                FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('pages/hero')
                    ->visibility('public')
                    ->image()
                    ->nullable(),
                self::backgroundField(),
            ])
            ->columns(2);
    }
}