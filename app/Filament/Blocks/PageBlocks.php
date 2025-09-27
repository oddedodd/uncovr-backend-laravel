<?php

namespace App\Filament\Blocks;

class PageBlocks
{
    public static function all(): array
    {
        return [
            HeroBlock::make(),
            TextBlock::make(),
            ImageBlock::make(),
            GalleryBlock::make(),
            VideoBlock::make(),
            SpacerBlock::make(),
        ];
    }
}