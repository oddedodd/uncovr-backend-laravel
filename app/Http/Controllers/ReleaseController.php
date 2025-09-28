<?php

namespace App\Http\Controllers;

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

        return response()->json(
            Release::where('artist_id', $artist->id)
                ->orderByDesc('created_at')
                ->paginate(20)
        );
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

        $release = Release::create($data);

        return response()->json($release, 201);
    }

    public function show(Request $request, Release $release)
    {
        $this->authorize('view', $release);
        return response()->json($release);
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
        ]);

        if (array_key_exists('status', $data)) {
            $data['published_at'] = $data['status'] === 'published' ? now() : null;
        }

        $release->update($data);

        return response()->json($release);
    }

    public function destroy(Request $request, Release $release)
    {
        $this->authorize('delete', $release);
        $release->delete();

        return response()->json(['message' => 'Release deleted']);
    }
}