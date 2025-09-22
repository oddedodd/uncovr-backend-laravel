<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Models\Release;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Knytt til Release (artist ser bare egne releases)
            Forms\Components\Select::make('release_id')
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

            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('La stå tom for å generere automatisk.')
                ->maxLength(255),

            // Cover-bilde
            Forms\Components\FileUpload::make('cover_image')
                ->label('Cover image')
                ->disk('public')           // sørg for at disk=public finnes
                ->directory('pages/covers') // lagrer filene her
                ->visibility('public')
                ->image()
                ->imageEditor()
                ->maxSize(4096)             // 4MB
                ->nullable(),

            // Rich text-innhold
            Forms\Components\RichEditor::make('content')
                ->label('Content')
                ->toolbarButtons([
                    'bold', 'italic', 'underline', 'strike',
                    'h2', 'h3', 'blockquote', 'link', 'orderedList', 'bulletList',
                    'codeBlock',
                ])
                ->columnSpanFull()
                ->nullable(),
        ])->columns(2);
    }
}