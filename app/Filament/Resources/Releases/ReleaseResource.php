<?php

namespace App\Filament\Resources\Releases;

use App\Filament\Resources\Releases\Pages\CreateRelease;
use App\Filament\Resources\Releases\Pages\EditRelease;
use App\Filament\Resources\Releases\Pages\ListReleases;
use App\Filament\Resources\Releases\Schemas\ReleaseForm;
use App\Filament\Resources\Releases\Tables\ReleasesTable;
use App\Models\Artist;
use App\Models\Label;
use App\Models\Release;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    // v4: må være string|\UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Releases';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        // Skjema-oppsett ligger i egen klasse – beholdes
        return ReleaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Tabell-oppsett ligger i egen klasse – beholdes
        return ReleasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Rollebasert filtrering:
     * - admin: ser alt
     * - label: ser releases for artister som tilhører labelen (owner_user_id = innlogget)
     * - artist: ser kun egne releases (artist.user_id = innlogget)
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('label')) {
            $labelId = Label::where('owner_user_id', $user->id)->value('id');

            if ($labelId) {
                return $query->whereHas('artist', fn (Builder $q) => $q->where('label_id', $labelId));
            }

            // Ingen label tilknyttet brukeren -> vis ingenting
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('artist')) {
            $artistId = Artist::where('user_id', $user->id)->value('id');

            if ($artistId) {
                return $query->where('artist_id', $artistId);
            }

            // Ingen artist tilknyttet brukeren -> vis ingenting
            return $query->whereRaw('1 = 0');
        }

        // Andre roller -> ingenting
        return $query->whereRaw('1 = 0');
    }

    // Auto-slug + published_at-håndtering (beholdt fra din versjon)
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['title'] ?? '')) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (array_key_exists('status', $data) && $data['status'] === 'published') {
            $data['published_at'] = now();
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['title'] ?? '')) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (array_key_exists('status', $data)) {
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReleases::route('/'),
            'create' => CreateRelease::route('/create'),
            'edit'   => EditRelease::route('/{record}/edit'),
        ];
    }
}