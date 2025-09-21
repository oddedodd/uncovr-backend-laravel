<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\Release;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Bruk evt. ny release_id (om den endres i skjema)
        $releaseId = $data['release_id'] ?? $this->getRecord()->release_id;

        // Unik slug innenfor release
        $base = !empty($data['slug'] ?? '')
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['title'] ?? ''));

        $base = $base !== '' ? $base : 'page';
        $slug = $base;
        $i = 1;

        while (
            Page::where('release_id', $releaseId)
                ->where('slug', $slug)
                ->whereKeyNot($this->getRecord()->getKey())
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        $data['slug'] = $slug;

        // Sett position hvis fortsatt ikke satt
        if (empty($data['position'] ?? null)) {
            $last = Page::where('release_id', $releaseId)->max('position') ?? 0;
            $data['position'] = $last + 1;
        }

        // (Valgfritt) Lås release-eierskap for artist
        if (auth()->user()?->hasRole('artist') && $releaseId) {
            $owned = Release::where('id', $releaseId)
                ->whereHas('artist', fn($q) => $q->where('user_id', auth()->id()))
                ->exists();

            if (!$owned) {
                abort(403, 'You cannot move this page to a release you do not own.');
            }
        }

        return $data;
    }
}