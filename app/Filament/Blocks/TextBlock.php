<?php

namespace App\Filament\Blocks;

use App\Filament\Blocks\Concerns\HasBackground;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;

class TextBlock
{
    use HasBackground;

    public static function make(): Block
    {
        return Block::make('text')
            ->label('Text')
            ->schema([
                RichEditor::make('html')
                    ->label('Content')
                    ->toolbarButtons([
                        'bold','italic','underline','strike',
                        'h2','h3','blockquote','link',
                        'orderedList','bulletList','codeBlock'
                    ])
                    ->required()
                    ->columnSpanFull(),
                self::backgroundField(),
            ]);
    }
}