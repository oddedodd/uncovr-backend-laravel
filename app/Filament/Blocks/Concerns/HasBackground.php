<?php

namespace App\Filament\Blocks\Concerns;

use Filament\Forms\Components\ColorPicker;

trait HasBackground
{
    protected static function backgroundField(string $label = 'Background'): ColorPicker
    {
        return ColorPicker::make('background_color')
            ->label($label)
            ->nullable();
    }
}