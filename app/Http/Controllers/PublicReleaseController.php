<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReleaseResource;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Http\Request;

class PublicReleaseController extends Controller
{
    // GET /api/v1/releases  (kun published)
    public function index(Request $request)
    {
        $allowedTypes = ['single', 'ep', 'album'];

        $types = collect(explode(',', (string) $request->query('type', '')))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter()
            ->values()
            ->all();

        // Behold kun gyldige typer
        $types = array_values(array_intersect($types, $allowedTypes));

        $query = Release::query()
            ->published()
            ->with(['artist:id,name,slug']) // valgfritt, hyggelig for klienten
            ->when(
                $request->filled('artist_id'),
                fn ($q) => $q->where('artist_id', (int) $request->query('artist_id'))
            )
            ->when(
                count($types) === 1,
                fn ($q) => $q->where('type', $types[0])
            )
            ->when(
                count($types) > 1,
                fn ($q) => $q->whereIn('type', $types)
            )
            ->orderByDesc('release_date')
            ->orderByDesc('published_at');

        return ReleaseResource::collection(
            $query->paginate(20)
        );
    }

    // GET /api/v1/artists/{artist}/releases/public  (kun published for gitt artist)
    public function byArtist(Request $request, Artist $artist)
    {
        $releases = Release::query()
            ->where('artist_id', $artist->id)
            ->published()
            ->with(['artist:id,name,slug'])
            ->orderByDesc('release_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return ReleaseResource::collection($releases);
    }

    // GET /api/v1/releases/slug/{slug}  (én published via slug)
    public function showBySlug(string $slug)
    {
        $release = Release::query()
            ->published()
            ->where('slug', $slug)
            ->with(['artist:id,name,slug'])
            ->firstOrFail();

        return new ReleaseResource($release);
    }
}