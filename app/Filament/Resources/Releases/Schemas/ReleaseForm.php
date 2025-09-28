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
use Filament\Schemas\Components\Group;   // 👈 Bytt til Group (flat, uten kort)
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Flat container uten kort/skygge, full bredde
                Group::make()
                    ->schema([
                        Select::make('artist_id')
                            ->label('Artist')
                            ->relationship(
                                name: 'artist',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = auth()->user();
                                    if (! $user) return;

                                    if ($user->hasRole('label')) {
                                        $labelId = Label::where('owner_user_id', $user->id)->value('id');
                                        $labelId
                                            ? $query->where('label_id', $labelId)
                                            : $query->whereRaw('1=0');
                                    }

                                    if ($user->hasRole('artist')) {
                                        $artistId = Artist::where('user_id', $user->id)->value('id');
                                        $artistId
                                            ? $query->where('id', $artistId)
                                            : $query->whereRaw('1=0');
                                    }
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => auth()->user()?->hasRole('artist') ?? false)
                            ->required(fn () => ! (auth()->user()?->hasRole('artist') ?? false))
                            ->default(function () {
                                $user = auth()->user();
                                if ($user && $user->hasRole('artist')) {
                                    return Artist::where('user_id', $user->id)->value('id');
                                }
                                return null;
                            })
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->helperText('La stå tom for å generere automatisk.')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        // 👇 NYTT: Type (Singel/EP/Album)
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'single' => 'Singel',
                                'ep'     => 'EP',
                                'album'  => 'Album',
                            ])
                            ->required()
                            ->columnSpan(1),

                        // 👇 NYTT: Status (Under arbeid/Publisert)
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Under arbeid',
                                'published' => 'Publisert',
                            ])
                            ->default('draft')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('spotify_url')
                            ->label('Spotify-lenke')
                            ->placeholder('https://open.spotify.com/album/... eller playlist/track')
                            ->helperText('Lim inn en lenke fra open.spotify.com (album/playlist/track).')
                            ->url()
                            ->maxLength(2048)
                            ->rule('regex:/^https?:\/\/open\.spotify\.com\/(album|playlist|track)\/[A-Za-z0-9]+(\?.*)?$/')
                            ->validationMessages([
                                'regex' => 'Lenken må være en gyldig open.spotify.com album/playlist/track-URL.',
                            ])
                            ->columnSpan(1),

                        FileUpload::make('cover_image')
                            ->label('Cover image')
                            ->disk('public')
                            ->directory('releases/covers')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->nullable()
                            ->columnSpan(1),

                        RichEditor::make('content')
                            ->label('Content')
                            ->toolbarButtons([
                                'bold','italic','underline','strike',
                                'h2','h3','blockquote','link','orderedList','bulletList','codeBlock',
                            ])
                            ->columnSpanFull()
                            ->nullable(),

                        // 👇 NYTT: Utgivelsesdato (ved siden av Published at)
                        DatePicker::make('release_date')
                            ->label('Utgivelsesdato')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->nullable()
                            ->columnSpan(1),

                        DatePicker::make('published_at')
                            ->label('Published at')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->nullable()
                            ->columnSpan(1),
                    ])
                    // 2 kolonner øverst, content på egen rad pga columnSpanFull()
                    ->columns(2)
                    ->columnSpanFull(), // 👈 svært viktig for full bredde
            ])
            ->columns(1); // ikke legg ekstra kolonner på rotskjemaet (unngå smal wrapper)
    }
}