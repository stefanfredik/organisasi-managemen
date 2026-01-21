<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeetingMinuteRequest;
use App\Http\Requests\UpdateMeetingMinuteRequest;
use App\Models\MeetingMinute;
use App\Models\MeetingMinuteAttachment;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MeetingMinuteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', MeetingMinute::class);

        $query = MeetingMinute::query()
            ->with('creator')
            ->latest();

        if ($request->filled('search')) {
            $query->where('agenda', 'like', '%' . $request->search . '%');
        }

        $minutes = $query->paginate(10)->withQueryString();

        return Inertia::render('MeetingMinutes/Index', [
            'minutes' => $minutes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', MeetingMinute::class);

        $members = \App\Models\Member::query()
            ->select('id', 'full_name')
            ->active()
            ->orderBy('full_name')
            ->get();

        return Inertia::render('MeetingMinutes/Create', [
            'members' => $members,
        ]);
    }

    public function store(StoreMeetingMinuteRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $minute = MeetingMinute::create($data);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('meeting-minutes/attachments', 'public');
                MeetingMinuteAttachment::create([
                    'meeting_minute_id' => $minute->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $this->activityLogger->logCreate(
            $minute,
            "Created meeting minute: {$minute->agenda}"
        );

        return redirect()->route('meeting-minutes.index')->with('success', 'Notulensi rapat berhasil dibuat.');
    }

    public function show(MeetingMinute $minute)
    {
        $this->authorize('view', $minute);

        $participantNames = [];
        $participantIds = [];
        if (is_array($minute->participants)) {
            $participantIds = $minute->participants;
        } elseif (is_string($minute->participants) && strlen($minute->participants) > 0) {
            $decoded = json_decode($minute->participants, true);
            if (is_array($decoded)) {
                $participantIds = $decoded;
            }
        }
        if (!empty($participantIds)) {
            $participantNames = \App\Models\Member::query()
                ->whereIn('id', $participantIds)
                ->pluck('full_name')
                ->all();
        }

        return Inertia::render('MeetingMinutes/Show', [
            'record' => $minute->load(['creator', 'attachments'])->toArray(),
            'participant_names' => $participantNames,
        ]);
    }

    public function edit(MeetingMinute $meetingMinute)
    {
        $this->authorize('update', $meetingMinute);

        $members = \App\Models\Member::query()
            ->select('id', 'full_name')
            ->active()
            ->orderBy('full_name')
            ->get();

        $meetingMinute->load('attachments');
        $minuteData = [
            'id' => $meetingMinute->id,
            'meeting_date' => optional($meetingMinute->meeting_date)->format('Y-m-d'),
            'agenda' => $meetingMinute->agenda,
            'participants' => is_array($meetingMinute->participants) ? $meetingMinute->participants : [],
            'notes' => $meetingMinute->notes,
            'attachments' => $meetingMinute->attachments,
        ];

        return Inertia::render('MeetingMinutes/Edit', [
            'minute' => $minuteData,
            'members' => $members,
        ]);
    }

    public function update(UpdateMeetingMinuteRequest $request, MeetingMinute $meetingMinute)
    {
        $data = $request->validated();
        $old = $meetingMinute->toArray();

        $meetingMinute->update($data);

        $this->activityLogger->logUpdate(
            $meetingMinute,
            "Updated meeting minute: {$meetingMinute->agenda}",
            ['old' => $old, 'new' => $meetingMinute->toArray()]
        );

        return redirect()->route('meeting-minutes.index')->with('success', 'Notulensi rapat berhasil diperbarui.');
    }

    public function destroy(MeetingMinute $meetingMinute)
    {
        $this->authorize('delete', $meetingMinute);

        $agenda = $meetingMinute->agenda;
        $meetingMinute->delete();

        $this->activityLogger->logDelete(
            $meetingMinute,
            "Deleted meeting minute: {$agenda}"
        );

        return redirect()->route('meeting-minutes.index')->with('success', 'Notulensi rapat berhasil dihapus.');
    }

    public function uploadAttachment(Request $request, MeetingMinute $meetingMinute)
    {
        $this->authorize('update', $meetingMinute);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif', 'max:10240'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $path = $file->store('meeting-minutes/attachments', 'public');
            MeetingMinuteAttachment::create([
                'meeting_minute_id' => $meetingMinute->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->activityLogger->logCustom(
            'upload',
            "Uploaded attachments for meeting minute: {$meetingMinute->agenda}",
            ['count' => count($request->file('files', []))]
        );

        return redirect()->back()->with('success', 'Lampiran berhasil diunggah.');
    }

    public function downloadAttachment(MeetingMinuteAttachment $attachment)
    {
        $this->authorize('view', $attachment->meetingMinute);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $this->activityLogger->logCustom(
            'download',
            "Downloaded attachment: {$attachment->original_name}",
            ['meeting_minute_id' => $attachment->meeting_minute_id]
        );

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}
