<?php

namespace App\Filament\Resources\Artists\Pages;

use App\Filament\Resources\Artists\ArtistResource;
use App\Models\Artist;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateArtist extends CreateRecord
{
    protected static string $resource = ArtistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Slug: bruk feltet hvis satt, ellers generer fra name
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['name'] ?? ''));

        $slug = $base !== '' ? $base : 'artist';
        $i = 1;

        while (Artist::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        // Om innlogget er "artist", knytt automatisk eierskap hvis ikke satt
        if (auth()->user()?->hasRole('artist') && empty($data['user_id'] ?? null)) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}