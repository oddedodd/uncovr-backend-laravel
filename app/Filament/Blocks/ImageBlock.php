<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

class ImageBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('image')
            ->label('Image')
            ->schema([
                FileUpload::make('src')
                    ->label('Image')
                    ->disk('public')
                    ->directory('pages/images')
                    ->visibility('public')
                    ->image()
                    ->required(),
                TextInput::make('alt')->maxLength(160),
                TextInput::make('caption')->maxLength(160),
                self::backgroundField(),
            ])
            ->columns(2);
    }
}