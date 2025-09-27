<?php

namespace App\Filament\Resources\Labels;

use App\Filament\Resources\Labels\Pages\CreateLabel;
use App\Filament\Resources\Labels\Pages\EditLabel;
use App\Filament\Resources\Labels\Pages\ListLabels;
use App\Models\Label;
use App\Models\User;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    // v4: må være string|\UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Labels';

    protected static ?string $recordTitleAttribute = 'name';

    // v4-signatur: Schema
    public static function form(Schema $schema): Schema
    {
        $isAdmin = fn () => auth()->user()?->hasRole('admin');

        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('La stå tom for å generere automatisk.')
                ->maxLength(255),

            // Eier-info (ny bruker opprettes på Create, oppdateres på Edit)
            Forms\Components\TextInput::make('owner_name')
                ->label('Owner name')
                ->maxLength(255)
                ->required($isAdmin)
                ->visible($isAdmin)
                ->dehydrated(), // sendes til Page

            Forms\Components\TextInput::make('owner_email')
                ->label('Owner email')
                ->email()
                ->required($isAdmin)
                ->visible($isAdmin)
                ->dehydrated()
                ->rule(function (?Label $record) {
                    // unik email i users-tabellen; ignorer nåværende eier ved redigering
                    $ignoreUserId = $record?->owner_user_id;
                    return Rule::unique('users', 'email')->ignore($ignoreUserId);
                }),

            Forms\Components\TextInput::make('owner_password')
                ->label('Owner password')
                ->password()
                ->revealable()
                ->visible($isAdmin)
                ->dehydrated()
                ->required(fn ($record) => $record === null && auth()->user()?->hasRole('admin'))
                ->helperText('La stå tom ved redigering for å beholde dagens passord.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable()
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('artists_count')
                    ->label('Artists')
                    ->counts('artists')
                    ->sortable(),
            ])
            // Raden er klikkbar → Edit (unngår Action-klasser)
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('label')) {
            return $query->where('owner_user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    // Slug fallback – Create
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['name'] ?? '')) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $data;
    }

    // Slug fallback – Save
    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug'] ?? '') && !empty($data['name'] ?? '')) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLabels::route('/'),
            'create' => Pages\CreateLabel::route('/create'),
            'edit'   => Pages\EditLabel::route('/{record}/edit'),
        ];
    }
}