<?php

namespace App\Filament\Resources\Releases\Schemas;

use App\Models\Artist;
use App\Models\Label;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Artist-tilknytning: avhengig av rolle
                Select::make('artist_id')
                    ->label('Artist')
                    ->relationship(
                        name: 'artist',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();

                            if (! $user) {
                                return;
                            }

                            if ($user->hasRole('admin')) {
                                // Admin: ingen ekstra filter
                                return;
                            }

                            if ($user->hasRole('label')) {
                                // Label: begrens til artister under labelen de eier
                                $labelId = Label::where('owner_user_id', $user->id)->value('id');
                                if ($labelId) {
                                    $query->where('label_id', $labelId);
                                } else {
                                    // Ingen label funnet -> vis tomt
                                    $query->whereRaw('1=0');
                                }
                                return;
                            }

                            if ($user->hasRole('artist')) {
                                // Artist: lås til egen artist
                                $artistId = Artist::where('user_id', $user->id)->value('id');
                                if ($artistId) {
                                    $query->where('id', $artistId);
                                } else {
                                    $query->whereRaw('1=0');
                                }
                                return;
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    // Skjul for artists (settes automatisk under)
                    ->hidden(fn () => auth()->user()?->hasRole('artist') ?? false)
                    // Påkrevd for alle som ser feltet
                    ->required(fn () => ! (auth()->user()?->hasRole('artist') ?? false))
                    // Default for artist-rolle
                    ->default(function () {
                        $user = auth()->user();
                        if ($user && $user->hasRole('artist')) {
                            return Artist::where('user_id', $user->id)->value('id');
                        }
                        return null;
                    }),

                TextInput::make('title')
                    ->label('Title')
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

                // Cover image på RELEASE
                FileUpload::make('cover_image')
                    ->label('Cover image')
                    ->disk('public')                // krever: php artisan storage:link
                    ->directory('releases/covers')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096)
                    ->nullable(),

                // Rich text content (lagres som TEXT/VARCHAR, ikke JSON)
                RichEditor::make('content')
                    ->label('Content')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3', 'blockquote', 'link', 'orderedList', 'bulletList', 'codeBlock',
                    ])
                    ->columnSpanFull()
                    ->nullable(),

                DatePicker::make('published_at')
                    ->label('Published at')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->nullable(),
            ])
            ->columns(2);
    }
}