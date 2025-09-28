<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Page;

class PublicPageController extends Controller
{
    public function byReleaseSlug(string $slug)
    {
        $release = Release::where('slug',$slug)
            ->where('status','published')
            ->firstOrFail();

        return response()->json(
            Page::where('release_id', $release->id)
                ->where('status','published')
                ->orderBy('position')
                ->get()
        );
    }
}