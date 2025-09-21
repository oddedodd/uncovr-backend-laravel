<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Release;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generer slug hvis tomt
        if (empty($data['slug']) && !empty($data['title'])) {
            $base = Str::slug($data['title']) ?: 'page';
            $slug = $base;
            $i = 1;

            $exists = fn ($s) => \App\Models\Page::where('slug', $s)->exists();
            while ($exists($slug)) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        // Artist kan kun bruke egne releases
        if (auth()->user()?->hasRole('artist')) {
            $releaseId = $data['release_id'] ?? null;

            if (!$releaseId) {
                throw new \RuntimeException('Release is required.');
            }

            $owned = Release::query()
                ->where('id', $releaseId)
                ->whereHas('artist', fn ($q) => $q->where('user_id', auth()->id()))
                ->exists();

            if (!$owned) {
                throw new \RuntimeException('You are not allowed to create a page for this release.');
            }
        }

        return $data;
    }
}