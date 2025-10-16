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
    
    protected array $featuredData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load featured release data for editing
        $record = $this->getRecord();
        if ($record && $record->featuredRelease) {
            $data['is_featured'] = true;
            $data['featured_display_order'] = $record->featuredRelease->display_order;
        } else {
            $data['is_featured'] = false;
            $data['featured_display_order'] = null;
        }

        return $data;
    }

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

        // Handle featured release data (remove from main data)
        $featuredData = [];
        if (isset($data['is_featured'])) {
            $featuredData['is_featured'] = $data['is_featured'];
            unset($data['is_featured']);
        }
        if (isset($data['featured_display_order'])) {
            $featuredData['display_order'] = $data['featured_display_order'];
            unset($data['featured_display_order']);
        }

        // Store featured data for after-save hook
        $this->featuredData = $featuredData;
        return $data;
    }

    protected function afterSave(): void
    {
        if (!empty($this->featuredData)) {
            $this->syncFeaturedRelease($this->record, $this->featuredData);
        }
    }

    private function syncFeaturedRelease($record, array $featuredData): void
    {
        $isFeatured = $featuredData['is_featured'] ?? false;
        $displayOrder = $featuredData['display_order'] ?? null;

        if ($isFeatured) {
            // Create or update featured release
            $featuredRelease = $record->featuredRelease;
            
            if (!$featuredRelease) {
                // Auto-assign display order if not provided
                if ($displayOrder === null) {
                    $maxOrder = \App\Models\FeaturedRelease::max('display_order') ?? 0;
                    $displayOrder = $maxOrder + 1;
                }

                \App\Models\FeaturedRelease::create([
                    'release_id' => $record->id,
                    'display_order' => $displayOrder,
                ]);
            } elseif ($displayOrder !== null) {
                // Update display order if provided
                $featuredRelease->update(['display_order' => $displayOrder]);
            }
        } else {
            // Remove from featured
            $record->featuredRelease?->delete();
        }
    }
}