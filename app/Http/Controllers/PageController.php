<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(Request $request, Release $release)
    {
        $this->authorize('viewAny', Page::class);

        if ($request->user()->hasRole('artist')) {
            abort_unless($release->artist?->user_id === $request->user()->id, 403);
        }

        return response()->json(
            $release->pages()->paginate(20)
        );
    }

    public function store(Request $request, Release $release)
    {
        $this->authorize('create', Page::class);

        if ($request->user()->hasRole('artist')) {
            abort_unless($release->artist?->user_id === $request->user()->id, 403);
        }

        $data = $request->validate([
            'title'     => ['required','string','max:255'],
            'slug'      => ['nullable','string','max:255'],
            'page_type' => ['nullable','string','max:50'],
            'status'    => ['nullable','in:draft,published'],
            'meta'      => ['nullable','array'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        // Unik innen release
        if (Page::where('release_id', $release->id)->where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . Str::random(6);
        }

        // plasser sist
        $lastPos = (int) Page::where('release_id', $release->id)->max('position');
        $data['position'] = $lastPos + 1;

        $data['release_id'] = $release->id;
        $data['status'] = $data['status'] ?? 'draft';

        $page = Page::create($data);

        return response()->json($page, 201);
    }

    public function show(Request $request, Page $page)
    {
        $this->authorize('view', $page);
        return response()->json($page);
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $data = $request->validate([
            'title'     => ['sometimes','string','max:255'],
            'slug'      => ['sometimes','string','max:255'],
            'page_type' => ['sometimes','string','max:50'],
            'position'  => ['sometimes','integer','min:1'],
            'status'    => ['sometimes','in:draft,published'],
            'meta'      => ['sometimes','array','nullable'],
        ]);

        if (isset($data['slug'])) {
            $exists = Page::where('release_id', $page->release_id)
                ->where('slug', $data['slug'])
                ->where('id', '!=', $page->id)
                ->exists();
            if ($exists) {
                $data['slug'] .= '-' . Str::random(6);
            }
        }

        $page->update($data);

        return response()->json($page);
    }

    public function destroy(Request $request, Page $page)
    {
        $this->authorize('delete', $page);
        $page->delete();

        return response()->json(['message' => 'Page deleted']);
    }
}