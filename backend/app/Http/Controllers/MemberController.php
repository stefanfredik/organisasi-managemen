<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Inertia\Inertia;

class MemberController extends Controller
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
        $this->authorize('viewAny', Member::class);

        $query = Member::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        $members = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Member::class);

        return Inertia::render('Members/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        DB::transaction(function () use ($data, &$member) {
            // Create User first
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make('password'), // Access default, user should change it
                'role' => User::ROLE_ANGGOTA,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);

            // Create Member linked to User
            $data['user_id'] = $user->id;
            $member = Member::create($data);

            // Log activity
            $this->activityLogger->logCreate(
                $member,
                "Created new member and user account: {$member->full_name} ({$member->member_code})"
            );
        });

        return redirect()->route('members.index')
            ->with('success', 'Anggota dan akun user berhasil ditambahkan. Password default: "password"');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $this->authorize('view', $member);

        $member->load(['user']);

        // Load contribution history with type information
        $contributions = $member->contributions()
            ->with('contributionType')
            ->latest()
            ->get()
            ->map(function ($contribution) {
                return [
                    'id' => $contribution->id,
                    'type_name' => $contribution->contributionType->name ?? '-',
                    'amount' => $contribution->amount,
                    'payment_date' => $contribution->payment_date,
                    'payment_method' => $contribution->payment_method,
                    'status' => $contribution->status,
                    'notes' => $contribution->notes,
                ];
            });

        // Load event participation with event details
        $events = $member->events()
            ->withPivot('registration_date', 'attendance_status', 'notes')
            ->latest('events.start_date')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'event_date' => $event->start_date,
                    'location' => $event->location,
                    'registration_date' => $event->pivot->registration_date,
                    'attendance_status' => $event->pivot->attendance_status,
                    'notes' => $event->pivot->notes,
                ];
            });

        return Inertia::render('Members/Show', [
            'member' => $member,
            'contributions' => $contributions,
            'events' => $events,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $this->authorize('update', $member);

        return Inertia::render('Members/Edit', [
            'member' => $member,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $oldData = $member->toArray();
        $member->update($data);

        // Log activity
        $this->activityLogger->logUpdate(
            $member,
            "Updated member: {$member->full_name} ({$member->member_code})",
            ['old' => $oldData, 'new' => $member->toArray()]
        );

        return redirect()->route('members.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        $this->authorize('delete', $member);

        $memberName = $member->full_name;
        $memberCode = $member->member_code;

        $member->delete();

        // Log activity
        $this->activityLogger->logDelete(
            $member,
            "Deleted member: {$memberName} ({$memberCode})"
        );

        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
