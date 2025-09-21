<?php

namespace App\Filament\Resources\Releases;

use App\Filament\Resources\Releases\Pages\CreateRelease;
use App\Filament\Resources\Releases\Pages\EditRelease;
use App\Filament\Resources\Releases\Pages\ListReleases;
use App\Filament\Resources\Releases\Schemas\ReleaseForm;
use App\Filament\Resources\Releases\Tables\ReleasesTable;
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

    // 👇 v4: må være string|\UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Releases';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        // Skjema-oppsett ligger i egen klasse generert av v4 (behold den)
        return ReleaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Tabell-oppsett ligger i egen klasse generert av v4 (behold den)
        return ReleasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // 🔒 Artist-brukere får bare se releases de eier (via artist.user_id)
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole('artist')) {
            $query->whereHas('artist', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }

    // 🪄 (Valgfritt) Auto-slug + håndter published_at
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['title'] ?? '')) {
            $data['slug'] = Str::slug($data['title']);
        }
        if (($data['status'] ?? 'draft') === 'published') {
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
            'create' => Pages\CreateRelease::route('/create'),
            'edit'   => Pages\EditRelease::route('/{record}/edit'),
        ];
    }
}