<?php

namespace App\Filament\Resources\Labels;

use App\Filament\Resources\Labels\Pages\CreateLabel;
use App\Filament\Resources\Labels\Pages\EditLabel;
use App\Filament\Resources\Labels\Pages\ListLabels;
use App\Models\Label;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Labels';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Meny: kun admin ser "Labels".
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Tilganger: kun admin kan liste/opprette/endre/slette/lese Labels.
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function canView($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Label')
                ->schema([
                    TextInput::make('name')
                        ->label('Label name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (blank($get('slug')) && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->helperText('La stå tom for å generere automatisk.')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    // Epost/passord for å automatisk opprette label-bruker (owner)
                    TextInput::make('owner_email')
                        ->label('Owner email')
                        ->email()
                        ->required()
                        ->dehydrated(),

                    TextInput::make('owner_password')
                        ->label('Owner password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->dehydrated(),
                ])
                ->columns(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('artists_count')
                    ->counts('artists')
                    ->label('Artists')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin ser alt. (Label/Artist har uansett ikke tilgang pga canViewAny / navigation)
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLabels::route('/'),
            'create' => CreateLabel::route('/create'),
            'edit'   => EditLabel::route('/{record}/edit'),
        ];
    }
}