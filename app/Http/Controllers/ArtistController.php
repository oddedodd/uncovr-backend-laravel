<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Artist::class);

        $user = $request->user();

        if ($user->hasRole('admin') || $user->hasRole('label')) {
            $q = Artist::query();
        } else {
            $q = Artist::where('user_id', $user->id);
        }

        return response()->json($q->orderBy('name')->paginate(20));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Artist::class);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','unique:artists,slug'],
            'bio'  => ['nullable','string'],
            'links'=> ['nullable','array'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Artist::where('slug',$data['slug'])->exists()) {
                $data['slug'] .= '-'.Str::random(6);
            }
        }

        $data['user_id'] = $request->user()->id;

        $artist = Artist::create($data);

        return response()->json($artist, 201);
    }

    public function show(Request $request, Artist $artist)
    {
        $this->authorize('view', $artist);
        return response()->json($artist);
    }

    public function update(Request $request, Artist $artist)
    {
        $this->authorize('update', $artist);

        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'slug' => ['sometimes','string','max:255','unique:artists,slug,'.$artist->id],
            'bio'  => ['sometimes','string','nullable'],
            'links'=> ['sometimes','array','nullable'],
        ]);

        $artist->update($data);

        return response()->json($artist);
    }

    public function destroy(Request $request, Artist $artist)
    {
        $this->authorize('delete', $artist);
        $artist->delete();

        return response()->json(['message' => 'Artist deleted']);
    }

    // 👇 behold dashboard hvis du vil
    public function dashboard(Request $request)
    {
        return response()->json([
            'message' => 'Welcome, artist!',
            'user' => $request->user(),
        ]);
    }
}