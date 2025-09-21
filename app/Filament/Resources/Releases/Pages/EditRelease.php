<?php

namespace App\Filament\Resources\Releases\Pages;

use App\Filament\Resources\Releases\ReleaseResource;
use App\Models\Artist;
use App\Models\Release;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditRelease extends EditRecord
{
    protected static string $resource = ReleaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Dersom artist: håndhev eierskap
        if (auth()->user()?->hasRole('artist')) {
            $myArtistId = Artist::where('user_id', auth()->id())->value('id');
            if (empty($data['artist_id']) || (int)$data['artist_id'] !== (int)$myArtistId) {
                $data['artist_id'] = $myArtistId;
            }
        }

        // Unik slug (globalt), ekskluder denne posten
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['title'] ?? ''));

        $base = $base !== '' ? $base : 'release';
        $slug = $base;
        $i = 1;

        while (
            Release::where('slug', $slug)
                ->whereKeyNot($this->getRecord()->getKey())
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        // published_at ut fra status
        if (array_key_exists('status', $data)) {
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
        }

        return $data;
    }
}