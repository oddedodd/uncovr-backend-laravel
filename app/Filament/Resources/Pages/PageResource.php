<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Filament\Resources\Pages\Tables\PagesTable;
use App\Models\Page;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    // v4: må være string|\UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        // Skjema-oppsett i egen klasse
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Grunnoppsett i egen klasse
        $table = PagesTable::configure($table);

        // Etterkonfigurer for manuell rekkefølge:
        return $table
            ->reorderable('position')   // dra-og-slipp lagres til kolonnen "position"
            ->defaultSort('position')   // vis i riktig rekkefølge
            ->paginated(false);         // vis alt for enkel sorting
    }

    public static function getRelations(): array
    {
        return [];
    }

    // 🔒 Artist-brukere ser bare sider for releases de eier
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole('artist')) {
            $query->whereHas('release.artist', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }

    // 🪄 Auto-slug + legg på neste ledige "position" ved create
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['title'] ?? '')) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (empty($data['position'] ?? null) && !empty($data['release_id'] ?? null)) {
            $last = Page::where('release_id', $data['release_id'])->max('position') ?? 0;
            $data['position'] = $last + 1;
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['title'] ?? '')) {
            $data['slug'] = Str::slug($data['title']);
        }
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit'   => EditPage::route('/{record}/edit'),
        ];
    }
}