<?php

namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages\CreateArtist;
use App\Filament\Resources\Artists\Pages\EditArtist;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Schemas\ArtistForm;
use App\Filament\Resources\Artists\Tables\ArtistsTable;
use App\Models\Artist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // v4: string|\UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Artists';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Skjul Artists i venstremenyen for 'artist'-rollen.
     * Vis for admin/label.
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['admin', 'label']);
    }

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

    /**
     * Artist-rolle ser kun sin egen Artist-record (om du tillater visning).
     * Admin/label ser alle.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

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