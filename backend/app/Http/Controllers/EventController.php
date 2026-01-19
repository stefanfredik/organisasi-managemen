<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventDocumentation;
use App\Models\Member;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
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
        $this->authorize('viewAny', Event::class);

        $events = Event::with('pic')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display a calendar view of events.
     */
    public function calendar()
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::all(['id', 'name', 'start_date', 'status']);

        return Inertia::render('Events/Calendar', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Event::class);

        return Inertia::render('Events/Create', [
            'members' => Member::active()->get(['id', 'full_name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create($request->validated() + ['created_by' => $request->user()->id]);

        $this->activityLogger->logCreate($event, "Menambah kegiatan baru: {$event->name}");

        return redirect()->route('events.show', $event)
            ->with('success', 'Kegiatan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $event->load(['pic', 'creator', 'participants', 'documentations']);

        return Inertia::render('Events/Show', [
            'event' => $event,
            'members' => Member::active()->get(['id', 'full_name', 'member_code']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return Inertia::render('Events/Edit', [
            'event' => $event,
            'members' => Member::active()->get(['id', 'full_name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        $this->activityLogger->logUpdate($event, "Memperbarui kegiatan: {$event->name}");

        return redirect()->route('events.show', $event)
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $eventName = $event->name;
        $event->delete();

        $this->activityLogger->logDelete($event, "Menghapus kegiatan: {$eventName}");

        return redirect()->route('events.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    /**
     * Upload documentation for the event.
     */
    public function uploadDocumentation(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store("events/{$event->id}/docs", 'public');
                $type = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';

                $event->documentations()->create([
                    'file_path' => $path,
                    'file_type' => $type,
                    'caption' => $request->caption,
                ]);
            }
        }

        $this->activityLogger->logUpdate($event, "Mengupload dokumentasi untuk kegiatan {$event->name}");

        return back()->with('success', 'Dokumentasi berhasil diunggah.');
    }

    /**
     * Delete documentation.
     */
    public function deleteDocumentation(Event $event, EventDocumentation $documentation)
    {
        $this->authorize('update', $event);

        Storage::disk('public')->delete($documentation->file_path);
        $documentation->delete();

        $this->activityLogger->logUpdate($event, "Menghapus dokumentasi untuk kegiatan {$event->name}");

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    /**
     * Add a participant to the event.
     */
    public function addParticipant(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'member_id' => 'required|exists:members,id',
            'notes' => 'nullable|string',
        ]);

        if ($event->participants()->where('member_id', $request->member_id)->exists()) {
            return back()->with('error', 'Anggota sudah terdaftar di kegiatan ini.');
        }

        if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
            return back()->with('error', 'Kapasitas peserta sudah penuh.');
        }

        $event->participants()->attach($request->member_id, [
            'registration_date' => now(),
            'attendance_status' => 'registered',
            'notes' => $request->notes,
        ]);

        $member = Member::find($request->member_id);
        $this->activityLogger->logUpdate($event, "Menambah peserta {$member->full_name} ke kegiatan {$event->name}");

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    /**
     * Remove a participant from the event.
     */
    public function removeParticipant(Event $event, Member $member)
    {
        $this->authorize('update', $event);

        $event->participants()->detach($member->id);

        $this->activityLogger->logUpdate($event, "Menghapus peserta {$member->full_name} dari kegiatan {$event->name}");

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    /**
     * Update participant attendance status.
     */
    public function updateParticipantStatus(Request $request, Event $event, Member $member)
    {
        $this->authorize('update', $event);

        $request->validate([
            'attendance_status' => 'required|in:registered,attended,absent',
        ]);

        $event->participants()->updateExistingPivot($member->id, [
            'attendance_status' => $request->attendance_status,
        ]);

        $this->activityLogger->logUpdate($event, "Memperbarui status kehadiran {$member->full_name} di kegiatan {$event->name} menjadi {$request->attendance_status}");

        return back()->with('success', 'Status kehadiran berhasil diperbarui.');
    }
}
