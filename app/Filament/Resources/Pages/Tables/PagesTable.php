<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),

                Tables\Columns\TextColumn::make('release.title')
                    ->label('Release')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('page_type')
                    ->label('Type'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since(),
            ])
            // Klikk på rad -> Edit
            ->recordUrl(fn ($record) => route('filament.admin.resources.pages.edit', ['record' => $record]));
    }
}   