<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Release;

class PublicPageController extends Controller
{
    /**
     * GET /api/v1/releases/slug/{slug}/pages
     * Hent publiserte sider for en gitt release basert på slug
     *
     * @param string $slug
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function byReleaseSlug($slug)
    {
        // Finn release basert på slug
        $release = Release::where('slug', $slug)->firstOrFail();

        // Sjekk at release er publisert
        abort_unless($release->status === 'published', 404);

        // Hent publiserte sider, sortert etter posisjon
        $pages = $release->pages()
            ->where('status', 'published')
            ->orderBy('position')
            ->get();

        // Returner JSON via PageResource
        return PageResource::collection($pages);
    }
}