<?php

namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages\CreateArtist;
use App\Filament\Resources\Artists\Pages\EditArtist;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Schemas\ArtistForm;
use App\Models\Artist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Artists';

    protected static ?string $recordTitleAttribute = 'name';

    // Meny/tilgang – kun admin og label
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }
    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }
    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }
    public static function canView($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label']);
    }

    // Rollefilter: label ser egne artister, admin ser alt, artist ser ingen
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->hasRole('artist')) {
            return $query->whereRaw('1=0');
        }

        if (auth()->check() && auth()->user()->hasRole('label')) {
            $query->whereHas('label', fn ($q) => $q->where('owner_user_id', auth()->id()));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return ArtistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            // Gjør radene klikkbare til edit, så slipper vi EditAction-klassen
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            ->paginated(true)
            // Ingen per-rad actions (for å unngå EditAction-klassen i ditt miljø)
            ->actions([])
            ->bulkActions([]); // kan slå på senere når vi vil
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