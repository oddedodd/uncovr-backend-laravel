<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Release;

class PublicPageController extends Controller
{
    // GET /api/v1/releases/{release:slug}/pages
    public function byReleaseSlug(Release $release)
    {
        // Bare publiserte releases
        abort_unless($release->status === 'published', 404);

        // Hent sider + blocks i riktig rekkefølge
        $release->load([
            'pages' => fn ($q) => $q->orderBy('position'),
            'pages.blocks' => fn ($q) => $q->orderBy('position'),
        ]);

        return PageResource::collection($release->pages);
    }
}