<?php

namespace App\Filament\Resources\Labels;

use App\Filament\Resources\Labels\Pages\CreateLabel;
use App\Filament\Resources\Labels\Pages\EditLabel;
use App\Filament\Resources\Labels\Pages\ListLabels;
use App\Models\Label;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
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

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

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
            Group::make()
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

                    // Eiernavn – vis eksisterende ved redigering
                    TextInput::make('owner_name')
                        ->label('Owner name')
                        ->required(fn ($record) => $record === null) // kun required ved opprettelse
                        ->afterStateHydrated(function ($component, ?Label $record) {
                            $component->state($record?->owner?->name);
                        })
                        ->dehydrated(), // tas med i $data (Create/ Edit)

                    // Epost – vis eksisterende ved redigering
                    TextInput::make('owner_email')
                        ->label('Owner email')
                        ->email()
                        ->required(fn ($record) => $record === null)
                        ->afterStateHydrated(function ($component, ?Label $record) {
                            $component->state($record?->owner?->email);
                        })
                        ->dehydrated(),

                    // Passord – ikke forhåndsutfylt; kun brukt hvis satt
                    TextInput::make('owner_password')
                        ->label('Owner password')
                        ->password()
                        ->revealable()
                        ->required(fn ($record) => $record === null)
                        ->afterStateHydrated(function ($component) {
                            $component->state(null); // aldri vis lagret passord
                        })
                        ->dehydrated(fn ($state) => filled($state)), // bare med hvis utfylt
                ])
                ->columns(2)
                ->columnSpanFull(),
        ])->columns(1);
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
            // Klikkbar rad til edit – ingen Actions-klasser.
            ->recordUrl(fn ($record) => self::getUrl('edit', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
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