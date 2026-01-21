<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::query()->with('creator')->latest();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience')) {
            $aud = $request->audience;
            if ($aud === 'all') {
                $query->where('target_audience', 'all');
            } else {
                $query->where(function ($q) use ($aud) {
                    $q->where('target_audience', 'all')
                        ->orWhere(function ($qq) use ($aud) {
                            $qq->where('target_audience', 'roles')
                                ->whereJsonContains('target_roles', $aud);
                        });
                });
            }
        }

        $announcements = $query->paginate(10)->withQueryString();

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'filters' => $request->only(['search', 'status', 'audience']),
            'roleOptions' => [
                ['value' => 'all', 'label' => 'Semua'],
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'ketua', 'label' => 'Ketua'],
                ['value' => 'bendahara', 'label' => 'Bendahara'],
                ['value' => 'sekretaris', 'label' => 'Sekretaris'],
                ['value' => 'anggota', 'label' => 'Anggota'],
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);

        return Inertia::render('Announcements/Create', [
            'roleOptions' => ['admin', 'ketua', 'bendahara', 'sekretaris', 'anggota'],
        ]);
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $announcement = Announcement::create($data);

        $this->activityLogger->logCreate(
            $announcement,
            "Created announcement: {$announcement->title}"
        );

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        return Inertia::render('Announcements/Edit', [
            'announcement' => $announcement,
            'roleOptions' => ['admin', 'ketua', 'bendahara', 'sekretaris', 'anggota'],
        ]);
    }

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        return Inertia::render('Announcements/Show', [
            'announcement' => $announcement->load('creator'),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $data = $request->validated();
        $old = $announcement->toArray();
        $announcement->update($data);

        $this->activityLogger->logUpdate(
            $announcement,
            "Updated announcement: {$announcement->title}",
            ['old' => $old, 'new' => $announcement->toArray()]
        );

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);

        $title = $announcement->title;
        $announcement->delete();

        $this->activityLogger->logDelete(
            $announcement,
            "Deleted announcement: {$title}"
        );

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
