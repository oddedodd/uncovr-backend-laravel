<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Release;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select as SelectField;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Hvilken release siden tilhører
            SelectField::make('release_id')
                ->label('Release')
                ->required()
                ->searchable()
                ->preload()
                ->options(function () {
                    $q = Release::query()->orderBy('title');

                    if (auth()->user()?->hasRole('artist')) {
                        $q->whereHas('artist', fn ($qq) => $qq->where('user_id', auth()->id()));
                    }

                    return $q->pluck('title', 'id')->all();
                })
                ->helperText(
                    auth()->user()?->hasRole('artist')
                        ? 'Du kan kun velge dine egne utgivelser.'
                        : 'Admin/label kan velge blant alle utgivelser.'
                ),

            TextInput::make('title')
                ->label('Page title')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label('Slug')
                ->helperText('La være tom for å generere automatisk.')
                ->maxLength(255),

            // Page-nivå bakgrunn (arves ned til blokker uten egen farge)
            ColorPicker::make('background_color')
                ->label('Page background')
                ->nullable()
                ->helperText('Arves av blokker som ikke har egen bakgrunn.'),

            // 🚀 Blocks
            Builder::make('blocks')
                ->label('Blocks')
                ->blocks([

                    // HERO
                    Builder\Block::make('hero')
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
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ])
                        ->columns(2),

                    // TEXT
                    Builder\Block::make('text')
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
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ]),

                    // IMAGE
                    Builder\Block::make('image')
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
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ])
                        ->columns(2),

                    // GALLERY
                    Builder\Block::make('gallery')
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
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ]),

                    // VIDEO
                    Builder\Block::make('video')
                        ->label('Video')
                        ->schema([
                            TextInput::make('url')
                                ->label('Video URL (YouTube/Vimeo)')
                                ->required(),
                            TextInput::make('caption')->maxLength(160),
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ]),

                    // SPACER
                    Builder\Block::make('spacer')
                        ->label('Spacer')
                        ->schema([
                            SelectField::make('size')
                                ->label('Size')
                                ->options([
                                    'sm' => 'Small',
                                    'md' => 'Medium',
                                    'lg' => 'Large',
                                ])
                                ->default('md'),
                            ColorPicker::make('background_color')
                                ->label('Background')
                                ->nullable(),
                        ]),
                ])
                ->collapsible()
                ->reorderable()
                ->columnSpanFull(),

        ])->columns(2);
    }
}