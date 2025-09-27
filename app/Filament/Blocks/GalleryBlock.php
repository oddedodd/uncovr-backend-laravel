<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

class GalleryBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('gallery')
            ->label('Gallery')
            ->schema([
                Repeater::make('items')
                    ->schema([
                        FileUpload::make('src')
                            ->label('Image')
                            ->disk('public')
                            ->directory('pages/gallery')
                            ->visibility('public')
                            ->image()
                            ->required(),
                        TextInput::make('alt')->maxLength(160),
                    ])
                    ->minItems(1)
                    ->reorderable(true)
                    ->collapsible(),
                self::backgroundField(),
            ]);
    }
}