<?php

namespace App\Filament\Resources\Releases\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

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

                // Featured status column (admin only)
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isFeatured())
                    ->visible(fn () => Auth::user()?->hasRole('admin') ?? false),
            ])
            ->filters([
                // Featured filter (admin only)
                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All releases')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured')
                    ->queries(
                        true: fn ($query) => $query->whereHas('featuredRelease'),
                        false: fn ($query) => $query->whereDoesntHave('featuredRelease'),
                    )
                    ->visible(fn () => Auth::user()?->hasRole('admin') ?? false),
            ])
            ->bulkActions([
                // Mark as Featured (admin only)
                BulkAction::make('mark_featured')
                    ->label('Mark as Featured')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()?->hasRole('admin') ?? false)
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            if (!$record->isFeatured()) {
                                $maxOrder = \App\Models\FeaturedRelease::max('display_order') ?? 0;
                                \App\Models\FeaturedRelease::create([
                                    'release_id' => $record->id,
                                    'display_order' => $maxOrder + 1,
                                ]);
                            }
                        }
                    }),

                // Remove from Featured (admin only)
                BulkAction::make('remove_featured')
                    ->label('Remove from Featured')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()?->hasRole('admin') ?? false)
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->featuredRelease?->delete();
                        }
                    }),
            ]);
    }
}