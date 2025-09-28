<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReleaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * NB: Beholder dagens felter uendret.
     *    Ekstra-felter: cover_image_url, is_published.
     *    Relasjoner (valgfritt): artist, pages – inkluderes kun hvis lastet.
     */
    public function toArray($request): array
    {
        return [
            // Primærfelter (som i dag)
            'id'            => $this->id,
            'artist_id'     => $this->artist_id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'cover_image'   => $this->cover_image,
            'content'       => $this->content,
            'spotify_url'   => $this->spotify_url,
            'type'          => $this->type,
            'status'        => $this->status,
            'release_date'  => optional($this->release_date)->toDateString(),
            'published_at'  => optional($this->published_at)->toIso8601String(),
            'meta'          => $this->meta,
            'created_at'    => optional($this->created_at)->toIso8601String(),
            'updated_at'    => optional($this->updated_at)->toIso8601String(),

            // Små, nyttige tillegg
            'cover_image_url' => $this->cover_image
                ? (str_starts_with($this->cover_image, 'http')
                    ? $this->cover_image
                    : Storage::disk('public')->url($this->cover_image))
                : null,

            'is_published' => $this->status === 'published',

            // Relasjoner – kun når de er eager-loaded
            'artist' => $this->whenLoaded('artist', function () {
                return [
                    'id'   => $this->artist->id,
                    'name' => $this->artist->name,
                    'slug' => $this->artist->slug,
                ];
            }),

            'pages' => $this->whenLoaded('pages', function () {
                return PageResource::collection($this->pages);
            }),
        ];
    }
}