<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReleaseResource;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReleaseController extends Controller
{
    public function index(Request $request, Artist $artist)
    {
        $this->authorize('viewAny', Release::class);

        if ($request->user()->hasRole('artist')) {
            abort_unless($artist->user_id === $request->user()->id, 403);
        }

        $releases = Release::where('artist_id', $artist->id)
            ->with(['artist:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return ReleaseResource::collection($releases);
    }

    public function store(Request $request, Artist $artist)
    {
        $this->authorize('create', Release::class);

        if ($request->user()->hasRole('artist')) {
            abort_unless($artist->user_id === $request->user()->id, 403);
        }

        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'type'         => ['required','in:album,single,ep'],
            'release_date' => ['nullable','date'],
            'slug'         => ['nullable','string','max:255','unique:releases,slug'],
            'meta'         => ['nullable','array'],
            'status'       => ['nullable','in:draft,published'],
            'spotify_url'  => ['nullable','url','max:2048'],
            'content'      => ['nullable','string'],
            'cover_image'  => ['nullable','string','max:2048'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
            if (Release::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . Str::random(6);
            }
        }

        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_at'] = now();
        }

        $data['artist_id'] = $artist->id;

        $release = Release::create($data)->load(['artist:id,name,slug']);

        return (new ReleaseResource($release))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Release $release)
    {
        $this->authorize('view', $release);

        $release->load(['artist:id,name,slug']);

        return new ReleaseResource($release);
    }

    public function update(Request $request, Release $release)
    {
        $this->authorize('update', $release);

        $data = $request->validate([
            'title'        => ['sometimes','string','max:255'],
            'type'         => ['sometimes','in:album,single,ep'],
            'release_date' => ['sometimes','date','nullable'],
            'slug'         => ['sometimes','string','max:255','unique:releases,slug,'.$release->id],
            'meta'         => ['sometimes','array','nullable'],
            'status'       => ['sometimes','in:draft,published'],
            'spotify_url'  => ['sometimes','nullable','url','max:2048'],
            'content'      => ['sometimes','nullable','string'],
            'cover_image'  => ['sometimes','nullable','string','max:2048'],
        ]);

        if (array_key_exists('status', $data)) {
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
        }

        $release->update($data);

        return new ReleaseResource($release->fresh()->load(['artist:id,name,slug']));
    }

    public function destroy(Request $request, Release $release)
    {
        $this->authorize('delete', $release);

        $release->delete();

        return response()->json(['message' => 'Release deleted']);
    }
}