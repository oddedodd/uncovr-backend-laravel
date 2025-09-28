<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Release;

class PublicReleaseController extends Controller
{
    // Alle publiserte (paginert)
    public function index()
    {
        return response()->json(
            Release::where('status', 'published')
                ->orderByDesc('published_at')
                ->paginate(20)
        );
    }

    // Publiserte for en gitt artist
    public function byArtist(Artist $artist)
    {
        return response()->json(
            Release::where('artist_id', $artist->id)
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->paginate(20)
        );
    }

    // Én publisert release etter slug
    public function showBySlug(string $slug)
    {
        $release = Release::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json($release);
    }
}