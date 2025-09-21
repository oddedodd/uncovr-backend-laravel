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
use Illuminate\Support\Str;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // v4 krever string|\UnitEnum|null (ikke ?string)
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Artists';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Skjul Artists i menyen for rollen "artist"
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
     * Artist-rolle ser kun sin egen Artist-record.
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

    // 🪄 Auto-slug ved create + eierskap
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data = static::ensureUniqueSlug($data);

        if (auth()->user()?->hasRole('artist') && empty($data['user_id'] ?? null)) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    // 🪄 Auto-slug ved update/save
    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::ensureUniqueSlug($data);
    }

    private static function ensureUniqueSlug(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['name'] ?? '')) {
            $base = Str::slug($data['name']);
        } else {
            $base = Str::slug((string) ($data['slug'] ?? ''));
        }

        $slug = $base ?: 'artist';
        $i = 1;

        while (Artist::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;
        return $data;
    }
}