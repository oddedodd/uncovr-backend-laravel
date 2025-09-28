<?php

namespace App\Filament\Resources\Releases\Pages;

use App\Filament\Resources\Releases\ReleaseResource;
use App\Models\Artist;
use App\Models\Release;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;   // 👈 viktig import
use Illuminate\Support\Str;

class CreateRelease extends CreateRecord
{
    protected static string $resource = ReleaseResource::class;

    // 👇 Full bredde (metode, ikke property)
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->hasRole('artist')) {
            $myArtistId = Artist::where('user_id', auth()->id())->value('id');
            if (empty($data['artist_id']) || (int) $data['artist_id'] !== (int) $myArtistId) {
                $data['artist_id'] = $myArtistId;
            }
        }

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

        $status = $data['status'] ?? 'draft';
        $data['published_at'] = $status === 'published' ? now() : null;

        return $data;
    }
}