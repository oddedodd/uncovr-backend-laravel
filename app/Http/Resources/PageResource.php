<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'release_id'       => $this->release_id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'background_color' => $this->background_color,
            // Bruker accessoren fra modellen: getBlocksWithResolvedBackgroundAttribute()
            'blocks'           => $this->blocks_with_resolved_background,
            'created_at'       => optional($this->created_at)->toIso8601String(),
            'updated_at'       => optional($this->updated_at)->toIso8601String(),
        ];
    }
}