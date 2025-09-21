<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\Release;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generator for slug (unik per release_id)
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['title'] ?? ''));

        $base = $base !== '' ? $base : 'page';
        $slug = $base;
        $i = 1;

        $releaseId = $data['release_id'] ?? null;

        // Hvis release_id mangler (skjema skal kreve det), prøv å avlede fra valgt release (fail-safe)
        if (!$releaseId && method_exists($this, 'getRecord')) {
            $releaseId = $this->getRecord()->release_id ?? null;
        }

        while (
            $releaseId &&
            Page::where('release_id', $releaseId)->where('slug', $slug)->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        // Sett position hvis ikke satt: sist i rekkefølgen for release
        if (empty($data['position'] ?? null) && $releaseId) {
            $last = Page::where('release_id', $releaseId)->max('position') ?? 0;
            $data['position'] = $last + 1;
        }

        // (Valgfritt) Hvis innlogget er artist: sikre at valgt release tilhører vedkommende
        if (auth()->user()?->hasRole('artist') && $releaseId) {
            // Filament-formen vår filtrerer allerede, men vi låser det likevel:
            $owned = Release::where('id', $releaseId)
                ->whereHas('artist', fn($q) => $q->where('user_id', auth()->id()))
                ->exists();

            if (!$owned) {
                abort(403, 'You cannot create pages for this release.');
            }
        }

        return $data;
    }
}