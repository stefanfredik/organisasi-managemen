<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Inertia\Inertia;

class PublicAlbumController extends Controller
{
    /**
     * Display a listing of public albums.
     */
    public function index()
    {
        $albums = Album::public()
            ->with(['event', 'photos'])
            ->withCount('photos')
            ->latest()
            ->paginate(12);

        return Inertia::render('Public/Gallery', [
            'albums' => $albums,
        ]);
    }

    /**
     * Display the specified public album.
     */
    public function show($slug)
    {
        $album = Album::where('slug', $slug)
            ->where('status', 'public')
            ->with(['event', 'photos'])
            ->firstOrFail();

        return Inertia::render('Public/AlbumDetail', [
            'album' => $album,
        ]);
    }
}
