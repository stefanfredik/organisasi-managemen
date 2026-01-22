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

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by email availability
        if ($request->filled('has_email')) {
            if ($request->has_email === 'yes') {
                $query->whereNotNull('email')->where('email', '!=', '');
            } elseif ($request->has_email === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('email')->orWhere('email', '');
                });
            }
        }

        // Filter by phone availability
        if ($request->filled('has_phone')) {
            if ($request->has_phone === 'yes') {
                $query->whereNotNull('phone')->where('phone', '!=', '');
            } elseif ($request->has_phone === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('phone')->orWhere('phone', '');
                });
            }
        }

        // Filter by BPJS status
        if ($request->filled('bpjs_health')) {
            if ($request->bpjs_health === 'yes') {
                $query->where('bpjs_health_active', true);
            } elseif ($request->bpjs_health === 'no') {
                $query->where('bpjs_health_active', false);
            }
        }
        if ($request->filled('bpjs_employment')) {
            if ($request->bpjs_employment === 'yes') {
                $query->where('bpjs_employment_active', true);
            } elseif ($request->bpjs_employment === 'no') {
                $query->where('bpjs_employment_active', false);
            }
        }

        // Filter by join date range
        if ($request->filled('join_start')) {
            $query->whereDate('join_date', '>=', $request->join_start);
        }
        if ($request->filled('join_end')) {
            $query->whereDate('join_date', '<=', $request->join_end);
        }

        // Filter by linked user account availability
        if ($request->filled('has_user')) {
            if ($request->has_user === 'yes') {
                $query->whereNotNull('user_id');
            } elseif ($request->has_user === 'no') {
                $query->whereNull('user_id');
            }
        }

        // Optional filter by occupation
        if ($request->filled('occupation')) {
            $query->where('occupation', $request->occupation);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['full_name', 'member_code', 'join_date', 'created_at', 'status'];
        if (in_array($sortBy, $allowedSort, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $members = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only([
                'search',
                'status',
                'gender',
                'has_email',
                'has_phone',
                'bpjs_health',
                'bpjs_employment',
                'join_start',
                'join_end',
                'has_user',
                'occupation',
                'sort_by',
                'sort_dir',
            ]),
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
            if (request()->hasFile('photo')) {
                $data['photo'] = request()->file('photo')->store('members/photos', 'public');
            }
            if (request()->hasFile('ktp_photo')) {
                $data['ktp_photo'] = request()->file('ktp_photo')->store('members/ktp', 'public');
            }
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
            ->with('type')
            ->latest()
            ->get()
            ->map(function ($contribution) {
                return [
                    'id' => $contribution->id,
                    'type_name' => $contribution->type->name ?? '-',
                    'amount' => $contribution->amount,
                    'payment_date' => $contribution->payment_date,
                    'payment_period' => $contribution->payment_period,
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

        // Load donation transactions
        $donationTransactions = $member->donationTransactions()
            ->with('donation')
            ->latest('donation_date')
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'program_name' => $tx->donation->program_name,
                    'amount' => $tx->amount,
                    'donation_date' => $tx->donation_date,
                    'status' => $tx->status,
                    'notes' => $tx->notes,
                ];
            });

        return Inertia::render('Members/Show', [
            'member' => $member,
            'contributions' => $contributions,
            'events' => $events,
            'donationTransactions' => $donationTransactions,
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
        if ($request->hasFile('ktp_photo')) {
            if ($member->ktp_photo) {
                Storage::disk('public')->delete($member->ktp_photo);
            }
            $data['ktp_photo'] = $request->file('ktp_photo')->store('members/ktp', 'public');
        }

        $oldData = $member->toArray();
        $member->update($data);

        // Sync linked user basic info (name, email, status)
        if ($member->user) {
            $member->user->name = $member->full_name;
            if (!empty($member->email)) {
                $member->user->email = $member->email;
            }
            $member->user->status = $member->status === 'active' ? \App\Models\User::STATUS_ACTIVE : \App\Models\User::STATUS_INACTIVE;
            $member->user->save();
        }

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
