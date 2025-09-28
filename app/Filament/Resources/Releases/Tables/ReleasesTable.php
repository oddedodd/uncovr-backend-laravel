<?php

namespace App\Filament\Resources\Releases\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                Tables\Columns\TextColumn::make('artist.name')
                    ->label('Artist')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('release_date')
                    ->date()
                    ->label('Release date'),

                Tables\Columns\TextColumn::make('published_at')
                    ->since()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->label('Updated'),
            ])
            ->filters([
                // legg gjerne til filtre senere
            ]);
            // Ingen ->actions() / ->bulkActions() nødvendig i v4 her
    }
}