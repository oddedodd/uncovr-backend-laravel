<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function dashboard(Request $request)
    {
        return response()->json([
            'message' => 'Welcome, artist!',
            'user' => $request->user()->only(['id', 'name', 'email']),
        ]);
    }
}