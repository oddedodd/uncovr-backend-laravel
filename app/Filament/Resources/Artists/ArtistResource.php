<?php

namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages\CreateArtist;
use App\Filament\Resources\Artists\Pages\EditArtist;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Schemas\ArtistForm;
use App\Filament\Resources\Artists\Tables\ArtistsTable;
use App\Models\Artist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    // Ikon i sidemenyen (enum støttes)
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // Menygruppe må være string|UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Artists';
    protected static ?int $navigationSort   = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ArtistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Artist-rolle ser kun egne rader
        if (auth()->user()?->hasRole('artist')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
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