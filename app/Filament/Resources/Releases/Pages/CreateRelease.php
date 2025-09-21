<?php

namespace App\Filament\Resources\Releases\Pages;

use App\Filament\Resources\Releases\ReleaseResource;
use App\Models\Artist;
use App\Models\Release;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateRelease extends CreateRecord
{
    protected static string $resource = ReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Sikre at artist_id tilhører innlogget artist (dersom bruker er artist)
        if (auth()->user()?->hasRole('artist')) {
            $myArtistId = Artist::where('user_id', auth()->id())->value('id');
            // Sett automatisk hvis ikke satt, eller overstyr til egen
            if (empty($data['artist_id']) || (int)$data['artist_id'] !== (int)$myArtistId) {
                $data['artist_id'] = $myArtistId;
            }
        }

        // Slug (globalt unik for releases)
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['title'] ?? ''));

        $base = $base !== '' ? $base : 'release';
        $slug = $base;
        $i = 1;

        while (Release::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        // published_at ut fra status
        $status = $data['status'] ?? 'draft';
        $data['published_at'] = $status === 'published' ? now() : null;

        return $data;
    }
}