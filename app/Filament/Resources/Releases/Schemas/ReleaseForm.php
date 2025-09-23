<?php

namespace App\Filament\Resources\Releases\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Models\Artist;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Admin kan velge artist; artister får dette satt automatisk annet sted
            Forms\Components\Select::make('artist_id')
                ->label('Artist')
                ->searchable()
                ->preload()
                ->options(fn () => Artist::query()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all()
                )
                ->visible(fn () => auth()->user()?->hasRole('admin'))
                ->required(fn () => auth()->user()?->hasRole('admin')),

            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('La stå tom for å generere automatisk.')
                ->maxLength(255),

            // ✨ Cover image på RELEASE
            Forms\Components\FileUpload::make('cover_image')
                ->label('Cover image')
                ->disk('public')                 // krever `php artisan storage:link`
                ->directory('releases/covers')
                ->visibility('public')
                ->image()
                ->imageEditor()
                ->maxSize(4096)
                ->nullable(),

            // ✨ Innhold / rich text på RELEASE
            Forms\Components\RichEditor::make('content')
                ->label('Content')
                ->toolbarButtons([
                    'bold','italic','underline','strike',
                    'h2','h3','blockquote','link','orderedList','bulletList','codeBlock',
                ])
                ->columnSpanFull()
                ->nullable(),

            Forms\Components\DatePicker::make('published_at')
                ->label('Published at')
                ->native(false)
                ->closeOnDateSelection()
                ->nullable(),

        ])->columns(2);
    }
}