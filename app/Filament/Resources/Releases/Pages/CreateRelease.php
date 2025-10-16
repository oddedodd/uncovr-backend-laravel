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

        // Store featured data for after-create hook
        if (!empty($featuredData)) {
            $data['_featured_data'] = $featuredData;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->data;
        if (isset($data['_featured_data'])) {
            $this->syncFeaturedRelease($this->record, $data['_featured_data']);
        }
    }

    private function syncFeaturedRelease($record, array $featuredData): void
    {
        $isFeatured = $featuredData['is_featured'] ?? false;
        $displayOrder = $featuredData['display_order'] ?? null;

        if ($isFeatured) {
            // Auto-assign display order if not provided
            if ($displayOrder === null) {
                $maxOrder = \App\Models\FeaturedRelease::max('display_order') ?? 0;
                $displayOrder = $maxOrder + 1;
            }

            \App\Models\FeaturedRelease::create([
                'release_id' => $record->id,
                'display_order' => $displayOrder,
            ]);
        }
    }
}