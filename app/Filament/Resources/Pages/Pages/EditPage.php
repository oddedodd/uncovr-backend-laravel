<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Handle published_at based on status
        if (array_key_exists('status', $data)) {
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
        }

        if (auth()->user()?->hasRole('artist')) {
            if (! optional(\App\Models\Release::find($data['release_id']))->artist
                ?->user_id === auth()->id()) {
                abort(403, 'Not allowed to attach to this release');
            }
        }

        return $data; // ← keep all fields
    }

    protected function getRedirectUrl(): string
    {
        // Redirect back to the release edit page after saving
        $releaseId = $this->record->release_id;
        return route('filament.admin.resources.releases.edit', ['record' => $releaseId]);
    }
}