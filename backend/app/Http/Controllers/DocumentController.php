<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);

        $query = Document::query()->with('uploader')->latest();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        $documents = $query->paginate(15)->withQueryString();

        // Get unique categories for filter
        $categories = Document::query()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'category']),
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Document::class);

        // Get existing categories for autocomplete
        $categories = Document::query()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('Documents/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreDocumentRequest $request)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $data['file_path'] = $path;
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $data['uploaded_by'] = $request->user()->id;

        $document = Document::create($data);

        $this->activityLogger->logCreate(
            $document,
            "Uploaded document: {$document->name}"
        );

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diunggah.');
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return Inertia::render('Documents/Show', [
            'document' => $document->load('uploader'),
        ]);
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        // Get existing categories for autocomplete
        $categories = Document::query()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $data = $request->validated();
        $old = $document->toArray();

        // Handle file upload if new file is provided
        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $data['file_path'] = $path;
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $document->update($data);

        $this->activityLogger->logUpdate(
            $document,
            "Updated document: {$document->name}",
            ['old' => $old, 'new' => $document->toArray()]
        );

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $name = $document->name;

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $this->activityLogger->logDelete(
            $document,
            "Deleted document: {$name}"
        );

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    public function download(Document $document)
    {
        $this->authorize('download', $document);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $this->activityLogger->log(
            'download',
            $document,
            "Downloaded document: {$document->name}"
        );

        return Storage::disk('public')->download($document->file_path, $document->name . '.' . $document->file_type);
    }
}
