<?php

namespace App\Filament\Resources\Artists\Pages;

use App\Filament\Resources\Artists\ArtistResource;
use App\Models\Artist;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditArtist extends EditRecord
{
    protected static string $resource = ArtistResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Basis for slug: input-slug hvis satt, ellers fra name
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['name'] ?? ''));

        $slug = $base !== '' ? $base : 'artist';
        $i = 1;

        // Unngå kollisjon med andre rader enn denne
        while (
            Artist::where('slug', $slug)
                ->whereKeyNot($this->getRecord()->getKey())
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        return $data;
    }
}