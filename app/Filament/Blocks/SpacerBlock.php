<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;

class SpacerBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('spacer')
            ->label('Spacer')
            ->schema([
                Select::make('size')
                    ->label('Size')
                    ->options([
                        'sm' => 'Small',
                        'md' => 'Medium',
                        'lg' => 'Large',
                    ])
                    ->default('md'),
                self::backgroundField(),
            ]);
    }
}