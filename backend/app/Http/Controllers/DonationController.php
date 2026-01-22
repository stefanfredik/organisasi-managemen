<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Member;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DonationController extends Controller
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
        $this->authorize('viewAny', Donation::class);

        $donations = Donation::when($request->search, function ($query, $search) {
            $query->where('program_name', 'like', "%{$search}%");
        })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Donations/Index', [
            'donations' => $donations,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Donation::class);

        return Inertia::render('Donations/Form', [
            'isEdit' => false,
            'donation' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Donation::class);

        $validated = $request->validate([
            'program_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_public' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['program_name']) . '-' . Str::random(5);
        $validated['created_by'] = $request->user()->id;

        $donation = Donation::create($validated);

        $this->activityLogger->logCreate($donation, "Membuat program donasi baru: {$donation->program_name}");

        return redirect()->route('donations.index')
            ->with('success', 'Program donasi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Donation $donation)
    {
        $this->authorize('view', $donation);

        $donation->load([
            'creator',
            'transactions' => function ($query) {
                $query->with('member')->latest();
            }
        ]);

        $members = Member::active()->select('id', 'full_name', 'member_code', 'email', 'phone')->get();

        return Inertia::render('Donations/Show', [
            'donation' => $donation,
            'members' => $members,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Donation $donation)
    {
        $this->authorize('update', $donation);

        return Inertia::render('Donations/Form', [
            'isEdit' => true,
            'donation' => $donation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
        $this->authorize('update', $donation);

        $validated = $request->validate([
            'program_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,completed,cancelled',
            'is_public' => 'boolean',
        ]);

        $donation->update($validated);

        $this->activityLogger->logUpdate($donation, "Memperbarui program donasi: {$donation->program_name}");

        return redirect()->route('donations.show', $donation)
            ->with('success', 'Program donasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Donation $donation)
    {
        $this->authorize('delete', $donation);

        $programName = $donation->program_name;
        $donation->delete();

        $this->activityLogger->logDelete($donation, "Menghapus program donasi: {$programName}");

        return redirect()->route('donations.index')
            ->with('success', 'Program donasi berhasil dihapus.');
    }

    /**
     * Record a donation transaction (Admin Manual Entry).
     */
    public function recordTransaction(Request $request, Donation $donation)
    {
        $this->authorize('recordTransaction', $donation);

        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'donor_name' => 'nullable|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'donor_phone' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'donation_date' => 'required|date',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'paid';
        $validated['verified_by'] = $request->user()->id;
        $validated['verified_at'] = now();

        $transaction = $donation->transactions()->create($validated);

        // Update collected_amount
        $donation->increment('collected_amount', $validated['amount']);

        $isAnonymous = $validated['is_anonymous'] ?? false;
        $name = $isAnonymous ? 'Hamba Allah' : ($validated['donor_name'] ?? 'Anonim');
        $this->activityLogger->logUpdate($donation, "Mencatat donasi manual sebesar " . number_format($validated['amount']) . " dari {$name}");

        return back()->with('success', 'Donasi berhasil dicatat.');
    }

    /**
     * Store member payment with proof.
     */
    public function storeMemberPayment(Request $request, Donation $donation)
    {
        $this->authorize('makePayment', $donation);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'donation_date' => 'required|date',
            'receipt' => 'required|file|image|max:2048',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
            'donor_name' => 'nullable|string|max:255',
        ]);

        $path = $request->file('receipt')->store('donation-receipts', 'public');

        $data = [
            'amount' => $validated['amount'],
            'donation_date' => $validated['donation_date'],
            'receipt_path' => $path,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'donor_name' => $validated['donor_name'] ?? $request->user()->name,
            'donor_email' => $request->user()->email,
        ];

        // If user is a member
        if ($request->user()->role === 'anggota') {
             $member = Member::where('user_id', $request->user()->id)->first();
             if ($member) {
                 $data['member_id'] = $member->id;
             }
        }

        $donation->transactions()->create($data);

        return back()->with('success', 'Pembayaran donasi berhasil dikirim. Menunggu verifikasi admin.');
    }

    /**
     * Verify a donation transaction.
     */
    public function verifyTransaction(Request $request, DonationTransaction $transaction)
    {
        $donation = $transaction->donation;
        $this->authorize('verifyTransaction', $donation);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        if ($validated['action'] === 'approve') {
            $transaction->update([
                'status' => 'paid',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $validated['notes'] ? ($transaction->notes . "\n[Admin]: " . $validated['notes']) : $transaction->notes,
            ]);

            // Increment collected amount
            $donation->increment('collected_amount', $transaction->amount);
            
            $this->activityLogger->logUpdate($donation, "Memverifikasi donasi sebesar " . number_format($transaction->amount));
            
            return back()->with('success', 'Donasi berhasil diverifikasi.');
        } else {
             $transaction->update([
                'status' => 'rejected',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $validated['notes'] ? ($transaction->notes . "\n[Admin Reject]: " . $validated['notes']) : $transaction->notes,
            ]);
            
            return back()->with('success', 'Donasi ditolak.');
        }
    }

    /**
     * Display a summary report of all donations.
     */
    public function report(Request $request)
    {
        $this->authorize('viewAny', Donation::class);

        $donations = Donation::withCount('transactions')
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'program_name' => $donation->program_name,
                    'target_amount' => (float) $donation->target_amount,
                    'collected_amount' => (float) $donation->collected_amount,
                    'status' => $donation->status,
                    'transactions_count' => $donation->transactions_count,
                    'progress' => $donation->target_amount > 0 ? round(($donation->collected_amount / $donation->target_amount) * 100, 2) : 0,
                ];
            });

        $stats = [
            'total_target' => (float) Donation::sum('target_amount'),
            'total_collected' => (float) Donation::sum('collected_amount'),
            'active_programs' => Donation::where('status', 'active')->count(),
            'total_donators' => DonationTransaction::count(),
        ];

        return Inertia::render('Donations/Report', [
            'donations' => $donations,
            'stats' => $stats,
        ]);
    }
}
