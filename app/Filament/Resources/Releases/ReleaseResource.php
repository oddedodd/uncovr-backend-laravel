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

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Admin ser alt
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Artist: kun releases for artist som eies av innlogget bruker
        if ($user->hasRole('artist')) {
            return $query->whereHas('artist', fn ($q) => $q->where('user_id', $user->id));
        }

        // Label: kun releases for artister under label som eies av innlogget bruker
        if ($user->hasRole('label')) {
            return $query->whereHas('artist', function ($q) use ($user) {
                $q->whereHas('label', fn ($lq) => $lq->where('owner_user_id', $user->id));
            });
        }

        // Andre roller: ingenting
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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }

    public static function canView($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'label', 'artist']);
    }
}