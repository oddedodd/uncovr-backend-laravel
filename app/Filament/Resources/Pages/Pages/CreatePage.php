<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate slug if empty, but keep all other fields intact
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // (Optional) ownership guard – ensure selected release belongs to this artist
        if (auth()->user()?->hasRole('artist')) {
            if (! optional(\App\Models\Release::find($data['release_id']))->artist
                ?->user_id === auth()->id()) {
                abort(403, 'Not allowed to attach to this release');
            }
        }

        return $data; // ← don’t drop cover_image or content
    }
}