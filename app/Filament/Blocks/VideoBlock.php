<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;

class VideoBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('video')
            ->label('Video')
            ->schema([
                TextInput::make('url')
                    ->label('Video URL (YouTube/Vimeo)')
                    ->required(),
                TextInput::make('caption')->maxLength(160),
                self::backgroundField(),
            ]);
    }
}