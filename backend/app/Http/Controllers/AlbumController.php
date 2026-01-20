<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Models\Album;
use App\Models\Event;
use App\Models\Photo;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AlbumController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Album::class);

        $albums = Album::with(['creator', 'event', 'photos'])
            ->withCount('photos')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Albums/Index', [
            'albums' => $albums,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Album::class);

        return Inertia::render('Albums/Create', [
            'events' => Event::published()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlbumRequest $request)
    {
        $this->authorize('create', Album::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $filename = Str::random(20) . '.' . $coverImage->getClientOriginalExtension();
            $path = $coverImage->storeAs('albums/covers', $filename, 'public');
            $data['cover_image'] = $path;
        }

        $album = Album::create($data);

        $this->activityLogger->logCreate($album, "Membuat album baru: {$album->name}");

        return redirect()->route('albums.show', $album)
            ->with('success', 'Album berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album)
    {
        $this->authorize('view', $album);

        $album->load(['creator', 'event', 'photos.uploader']);

        return Inertia::render('Albums/Show', [
            'album' => $album,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Album $album)
    {
        $this->authorize('update', $album);

        $album->load(['event']);

        return Inertia::render('Albums/Edit', [
            'album' => $album,
            'events' => Event::published()->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $this->authorize('update', $album);

        $data = $request->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }

            $coverImage = $request->file('cover_image');
            $filename = Str::random(20) . '.' . $coverImage->getClientOriginalExtension();
            $path = $coverImage->storeAs('albums/covers', $filename, 'public');
            $data['cover_image'] = $path;
        }

        $album->update($data);

        $this->activityLogger->logUpdate($album, "Memperbarui album: {$album->name}");

        return redirect()->route('albums.show', $album)
            ->with('success', 'Album berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Album $album)
    {
        $this->authorize('delete', $album);

        $albumName = $album->name;

        // Delete cover image
        if ($album->cover_image) {
            Storage::disk('public')->delete($album->cover_image);
        }

        // Delete all photos in the album
        foreach ($album->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $album->delete();

        $this->activityLogger->logDelete($album, "Menghapus album: {$albumName}");

        return redirect()->route('albums.index')
            ->with('success', 'Album berhasil dihapus.');
    }

    /**
     * Upload photos to the album.
     */
    public function uploadPhotos(Request $request, Album $album)
    {
        $this->authorize('uploadPhotos', $album);

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string|max:500',
        ]);

        $uploadedCount = 0;

        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            $descriptions = $request->input('descriptions', []);

            foreach ($photos as $index => $photo) {
                $originalName = $photo->getClientOriginalName();
                $filename = Str::random(20) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs("albums/{$album->id}/photos", $filename, 'public');

                Photo::create([
                    'album_id' => $album->id,
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'file_path' => $path,
                    'file_size' => $photo->getSize(),
                    'description' => $descriptions[$index] ?? null,
                    'order' => $album->photos()->count() + $index,
                    'uploaded_by' => $request->user()->id,
                ]);

                $uploadedCount++;
            }
        }

        $this->activityLogger->logUpdate($album, "Mengunggah {$uploadedCount} foto ke album: {$album->name}");

        return back()->with('success', "{$uploadedCount} foto berhasil diunggah.");
    }

    /**
     * Delete a photo from the album.
     */
    public function deletePhoto(Album $album, Photo $photo)
    {
        $this->authorize('deletePhotos', $album);

        // Ensure the photo belongs to the album
        if ($photo->album_id !== $album->id) {
            return back()->with('error', 'Foto tidak ditemukan dalam album ini.');
        }

        // Delete the file
        Storage::disk('public')->delete($photo->file_path);

        $photo->delete();

        $this->activityLogger->logUpdate($album, "Menghapus foto dari album: {$album->name}");

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    /**
     * Update photo order.
     */
    public function updatePhotoOrder(Request $request, Album $album)
    {
        $this->authorize('update', $album);

        $request->validate([
            'photos' => 'required|array',
            'photos.*.id' => 'required|exists:photos,id',
            'photos.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->photos as $photoData) {
            Photo::where('id', $photoData['id'])
                ->where('album_id', $album->id)
                ->update(['order' => $photoData['order']]);
        }

        $this->activityLogger->logUpdate($album, "Mengubah urutan foto di album: {$album->name}");

        return back()->with('success', 'Urutan foto berhasil diperbarui.');
    }

    /**
     * Update photo description.
     */
    public function updatePhotoDescription(Request $request, Album $album, Photo $photo)
    {
        $this->authorize('update', $album);

        // Ensure the photo belongs to the album
        if ($photo->album_id !== $album->id) {
            return back()->with('error', 'Foto tidak ditemukan dalam album ini.');
        }

        $request->validate([
            'description' => 'nullable|string|max:500',
        ]);

        $photo->update([
            'description' => $request->description,
        ]);

        return back()->with('success', 'Deskripsi foto berhasil diperbarui.');
    }
}
