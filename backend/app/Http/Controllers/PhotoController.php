<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download a photo.
     */
    public function download(Photo $photo)
    {
        // Check if user can view the album
        $this->authorize('view', $photo->album);

        if (!Storage::disk('public')->exists($photo->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($photo->file_path, $photo->original_name);
    }

    /**
     * Get photo details.
     */
    public function show(Photo $photo)
    {
        $this->authorize('view', $photo->album);

        $photo->load(['album', 'uploader']);

        return response()->json([
            'photo' => $photo,
        ]);
    }
}
