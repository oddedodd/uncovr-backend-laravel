<?php

namespace App\Filament\Resources\Artists\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ArtistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner (User)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('bio')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->label('Created'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->label('Updated'),
            ])
            ->filters([
                // legg gjerne til filtre senere
            ]);
            // Ingen ->actions() / ->bulkActions() her – v4 håndterer Edit/Slett via siden.
    }
}