<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReleaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        // Cover-image: prøv å returnere en full URL hvis det finnes
        $coverUrl = null;
        if ($this->cover_image) {
            try {
                // Storage::disk('public')->url(...) gir vanligvis "/storage/xxx"
                $storageUrl = Storage::disk('public')->url($this->cover_image);
                // url(...) gjør det til absolutt URL
                $coverUrl = url($storageUrl);
            } catch (\Throwable $e) {
                // fallback: returner rå path hvis noe går galt
                $coverUrl = $this->cover_image;
            }
        }

        return [
            'id'           => $this->id,
            'artist'       => $this->whenLoaded('artist', function () {
                return [
                    'id'   => $this->artist?->id,
                    'name' => $this->artist?->name,
                    'slug' => $this->artist?->slug,
                ];
            }),
            'title'        => $this->title,
            'slug'         => $this->slug,
            'type'         => $this->type,
            'status'       => $this->status,
            'release_date' => optional($this->release_date)->toDateString(),
            'published_at' => optional($this->published_at)->toIso8601String(),
            'cover_image'  => $coverUrl,
            'spotify_url'  => $this->spotify_url,
            'content'      => $this->content,
            // Pages inkluderes bare hvis controlleren eager-loadet relationen:
            'pages'        => \App\Http\Resources\PageResource::collection($this->whenLoaded('pages')),
            'meta'         => $this->meta,
            'created_at'   => optional($this->created_at)->toIso8601String(),
            'updated_at'   => optional($this->updated_at)->toIso8601String(),
        ];
    }
}