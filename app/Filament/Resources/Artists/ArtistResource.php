<?php

namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages\CreateArtist;
use App\Filament\Resources\Artists\Pages\EditArtist;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Schemas\ArtistForm;
// use App\Filament\Resources\Artists\Tables\ArtistsTable; // ikke brukt i denne filen
use App\Models\Artist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // v4: tillatt type for navigationGroup
    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Artists';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Artist-rolle: kun sin egen artist
        if (auth()->check() && auth()->user()->hasRole('artist')) {
            $query->where('user_id', auth()->id());
        }

        // Label-rolle: kun artister som tilhører label eid av innlogget bruker
        if (auth()->check() && auth()->user()->hasRole('label')) {
            $query->whereHas('label', function ($q) {
                $q->where('owner_user_id', auth()->id());
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        // Skjema defineres i egen klasse
        return ArtistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('label.name')
                    ->label('Label')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('releases_count')
                    ->counts('releases')
                    ->label('Releases')
                    ->sortable(),
            ])
            // Gjør rad klikkbar til Edit i stedet for å bruke EditAction
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            // Ingen rad- eller bulk-actions (unngår EditAction / DeleteAction klasser)
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListArtists::route('/'),
            'create' => CreateArtist::route('/create'),
            'edit'   => EditArtist::route('/{record}/edit'),
        ];
    }
}