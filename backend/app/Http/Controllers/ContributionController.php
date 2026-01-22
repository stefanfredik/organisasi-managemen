<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\ContributionType;
use App\Models\Member;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContributionMatrixExport;

class ContributionController extends Controller
{
    public function getMemberStatus(Request $request, ContributionType $contributionType)
    {
        $user = auth()->user();
        
        try {
            // If admin is viewing a member's status (optional feature), allow generic member selection
            if ($request->has('member_id') && in_array($user->role, ['admin', 'bendahara'])) {
                 $member = Member::findOrFail($request->member_id);
            } else {
                 $member = Member::where('user_id', $user->id)->first();
                 if (!$member) {
                     return response()->json(['message' => 'Akun Anda tidak terhubung dengan data Anggota. Hubungi Admin.'], 404);
                 }
            }
        } catch (\Exception $e) {
             return response()->json(['message' => 'Data anggota tidak ditemukan.'], 404);
        }

        $query = Contribution::where('member_id', $member->id)
                             ->where('contribution_type_id', $contributionType->id)
                             ->where('status', '!=', 'rejected'); // Active payments
        
        $contributions = $query->get();

        if ($contributionType->period === 'once') {
             $paid = $contributions->where('status', 'paid')->first();
             $pending = $contributions->where('status', 'pending')->first();
             
             return response()->json([
                 'type' => 'once',
                 'status' => $paid ? 'paid' : ($pending ? 'pending' : 'unpaid'),
                 'contribution' => $paid ?? $pending,
             ]);
        }

        // Periodic
        $startDate = $contributionType->start_date 
            ? Carbon::parse($contributionType->start_date) 
            : Carbon::parse($contributionType->created_at)->startOfMonth();
        
        if ($contributionType->end_date) {
            $endDate = Carbon::parse($contributionType->end_date);
        } else {
             // For open-ended, show up to current time + 1 period? 
             // Or just up to now.
             $endDate = Carbon::now();
             if ($contributionType->period === 'monthly') $endDate->endOfMonth();
             if ($contributionType->period === 'yearly') $endDate->endOfYear();
        }

        $periods = [];
        $current = $startDate->copy();
        
        $paidMap = $contributions->keyBy('payment_period'); // Assumes payment_period matches generated key
        
        $totalPeriods = 0;
        $paidPeriods = 0;

        // Safety break to prevent infinite loops if logic fails
        $maxIterations = 500;
        $iterations = 0;

        while ($current <= $endDate && $iterations < $maxIterations) {
            $key = '';
            $label = '';
            $periodDueDate = null;
            
            if ($contributionType->period === 'monthly') {
                $key = $current->format('Y-m');
                $label = $current->isoFormat('MMMM Y');
                if ($contributionType->recurring_day) {
                    // Try to set day, clamp to EndOfMonth
                    $d = $current->copy()->day(min($contributionType->recurring_day, $current->daysInMonth));
                    $periodDueDate = $d->format('Y-m-d');
                }
                $next = $current->copy()->addMonth();
            } elseif ($contributionType->period === 'weekly') {
                $key = $current->format('o-W'); // ISO Year and Week
                $label = 'Minggu ke-' . $current->weekOfYear . ' ' . $current->year;
                 // Calculate due date based on recurring_day (1=Mon, 7=Sun)
                if ($contributionType->recurring_day) {
                    $d = $current->copy()->startOfWeek()->addDays($contributionType->recurring_day - 1);
                    $periodDueDate = $d->format('Y-m-d');
                }
                $next = $current->copy()->addWeek();
            } elseif ($contributionType->period === 'yearly') {
                $key = $current->format('Y');
                $label = $current->format('Y');
                 if ($contributionType->due_date) {
                    // Use the Month/Day from due_date for this year
                    $baseDue = Carbon::parse($contributionType->due_date);
                    $periodDueDate = $current->copy()->month($baseDue->month)->day($baseDue->day)->format('Y-m-d');
                }
                $next = $current->copy()->addYear();
            } else {
                 $next = $current->copy()->addDay(); // Fallback
            }

            $status = 'unpaid';
            $contribution = null;
            
            if ($paidMap->has($key)) {
                $c = $paidMap->get($key);
                $status = $c->status;
                $contribution = $c;
                if ($status === 'paid') $paidPeriods++;
            }
            
            $periods[] = [
                'period' => $key,
                'label' => $label,
                'due_date' => $periodDueDate,
                'status' => $status,
                'contribution' => $contribution
            ];
            
            $totalPeriods++;
            $current = $next;
            $iterations++;
        }

        return response()->json([
            'type' => $contributionType->period,
            'periods' => $periods,
            'summary' => [
                'total' => $totalPeriods,
                'paid' => $paidPeriods,
                'percentage' => $totalPeriods > 0 ? round(($paidPeriods / $totalPeriods) * 100, 1) : 0,
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = Contribution::with(['member' => function ($query) {
            $query->withTrashed();
        }, 'type', 'wallet', 'verifier']);

        if (in_array(auth()->user()->role, ['member', 'anggota'])) {
            $member = Member::where('user_id', auth()->id())->first();
            if ($member) {
                $query->where('member_id', $member->id);
            } else {
                // If no linked member, show empty list for safety
                $query->whereRaw('1 = 0');
            }
        } else {
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('member', function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('member_code', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('contribution_type_id')) {
                $query->where('contribution_type_id', $request->input('contribution_type_id'));
            }
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->input('payment_method'));
            }
            if ($request->filled('wallet_id')) {
                $query->where('wallet_id', $request->input('wallet_id'));
            }
            if ($request->filled('payment_period')) {
                $query->where('payment_period', $request->input('payment_period'));
            }
            if ($request->filled('start_date')) {
                $query->whereDate('payment_date', '>=', $request->input('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->whereDate('payment_date', '<=', $request->input('end_date'));
            }
            if ($request->filled('min_amount')) {
                $query->where('amount', '>=', (float) $request->input('min_amount'));
            }
            if ($request->filled('max_amount')) {
                $query->where('amount', '<=', (float) $request->input('max_amount'));
            }
        }

        $types = ContributionType::where('is_active', true)->get();

        if (in_array(auth()->user()->role, ['member', 'anggota'])) {
             $member = Member::where('user_id', auth()->id())->first();
             if ($member) {
                 foreach ($types as $type) {
                     $type->user_progress = $this->calculateTypeProgress($member, $type);
                 }
             }
        }

        return Inertia::render('Contributions/Index', [
            'contributions' => $query->latest()->paginate(10)->withQueryString(),
            'types' => $types,
            'wallets' => Wallet::where('is_active', true)->get(),
            'members' => Member::active()->get(['id', 'full_name', 'member_code']),
            'filters' => [
                'search' => $request->input('search'),
                'contribution_type_id' => $request->input('contribution_type_id'),
                'status' => $request->input('status'),
                'payment_method' => $request->input('payment_method'),
                'wallet_id' => $request->input('wallet_id'),
                'payment_period' => $request->input('payment_period'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'min_amount' => $request->input('min_amount'),
                'max_amount' => $request->input('max_amount'),
            ],
        ]);
    }

    public function getUnpaidMembers(Request $request)
    {
        $contributionTypeId = $request->query('contribution_type_id');
        $paymentPeriod = $request->query('payment_period');

        if (!$contributionTypeId) {
            return response()->json([]);
        }

        // Get the contribution type to check if it's recurring
        $contributionType = ContributionType::find($contributionTypeId);

        if (!$contributionType) {
            return response()->json([]);
        }

        // Get all active members
        $allMembers = Member::active()->get(['id', 'full_name', 'member_code']);

        // Build query to get members who have already paid
        $paidQuery = Contribution::where('contribution_type_id', $contributionTypeId)
            ->where('status', 'paid');

        // For one-time contributions, check all paid records
        if ($contributionType->period === 'once') {
            // No additional filter needed - once paid, always paid
        }
        // For recurring contributions, only check the specific payment period
        else {
            if ($paymentPeriod) {
                // Only get members who paid for THIS specific period
                // This ignores old records with NULL payment_period
                $paidQuery->where('payment_period', $paymentPeriod);
            } else {
                // If no payment period provided for recurring, return empty
                // This prevents showing all members as paid
                return response()->json($allMembers);
            }
        }

        $paidMemberIds = $paidQuery->pluck('member_id')->toArray();

        // Filter out members who have already paid
        $unpaidMembers = $allMembers->filter(function ($member) use ($paidMemberIds) {
            return !in_array($member->id, $paidMemberIds);
        })->values();

        return response()->json($unpaidMembers);
    }

    public function store(Request $request)
    {
        $userRole = auth()->user()->role;
        $validated = $request->validate([
            'member_id' => in_array($userRole, ['member', 'anggota']) ? 'nullable|exists:members,id' : 'required|exists:members,id',
            'contribution_type_id' => 'required|exists:contribution_types,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_period' => 'nullable|string',
            'payment_method' => 'nullable|in:transfer,cash',
            'wallet_id' => 'nullable|exists:wallets,id',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|image|max:2048',
        ]);

        if (in_array($userRole, ['member', 'anggota'])) {
            $member = Member::where('user_id', auth()->id())->first();
            if (!$member) {
                return redirect()->back()->with('error', 'Akun anggota Anda belum terhubung ke data Member. Silakan hubungi admin.');
            }
            $validated['member_id'] = $member->id;
        }

        // Get wallet from contribution type
        $contributionType = ContributionType::findOrFail($validated['contribution_type_id']);

        if ($contributionType->wallet_id) {
            $validated['wallet_id'] = $contributionType->wallet_id;
        } else {
            // Allow admin/treasurer to choose wallet when type is not linked
            if (in_array(auth()->user()->role, ['admin', 'bendahara']) && $request->wallet_id) {
                $validated['wallet_id'] = $request->wallet_id;
            } else {
                return redirect()->back()->with('error', 'Jenis iuran ini belum memiliki dompet tujuan. Silakan hubungi administrator.');
            }
        }

        // Force member/anggota to transfer method; allow admin/treasurer choice
        if (in_array($userRole, ['member', 'anggota'])) {
            $validated['payment_method'] = 'transfer';
        } else {
            $validated['payment_method'] = $validated['payment_method'] ?? 'cash';
        }

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        Contribution::create($validated);

        return redirect()->back()->with('success', 'Pembayaran iuran berhasil dicatat. Menunggu verifikasi.');
    }

    public function verify(Request $request, Contribution $contribution)
    {
        if ($contribution->status !== 'pending') {
            return redirect()->back()->with('error', 'Pembayaran sudah terverifikasi atau ditolak.');
        }

        $request->validate([
            'status' => 'required|in:paid,rejected',
        ]);

        DB::transaction(function () use ($request, $contribution) {
            $contribution->update([
                'status' => $request->status,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            if ($request->status === 'paid') {
                $contribution->wallet->increment('balance', $contribution->amount);
            }
        });

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function update(Request $request, Contribution $contribution)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_period' => 'nullable|string',
            'payment_method' => 'required|in:transfer,cash',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|image|max:2048',
        ]);

        $oldAmount = $contribution->amount;

        $receiptPath = $contribution->receipt_path;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        DB::transaction(function () use ($validated, $contribution, $oldAmount, $receiptPath) {
            if ($contribution->status === 'paid' && $contribution->wallet) {
                $diff = $validated['amount'] - $oldAmount;
                if ($diff > 0) {
                    $contribution->wallet->increment('balance', $diff);
                } elseif ($diff < 0) {
                    $contribution->wallet->decrement('balance', abs($diff));
                }
            }

            $contribution->update([
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_period' => $validated['payment_period'] ?? null,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'receipt_path' => $receiptPath,
            ]);
        });

        return redirect()->back()->with('success', 'Data iuran berhasil diperbarui.');
    }

    public function destroy(Contribution $contribution)
    {
        DB::transaction(function () use ($contribution) {
            if ($contribution->status === 'paid') {
                $contribution->wallet->decrement('balance', $contribution->amount);
            }
            $contribution->delete();
        });

        return redirect()->back()->with('success', 'Data pembayaran berhasil dihapus.');
    }

    public function storeBulk(Request $request)
    {
        $userRole = auth()->user()->role;
        $validated = $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:members,id',
            'contribution_type_id' => 'required|exists:contribution_types,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_period' => 'nullable|string',
            'periods' => 'nullable|array',
            'periods.*' => 'string',
            'payment_method' => 'nullable|in:transfer,cash',
            'wallet_id' => 'nullable|exists:wallets,id',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|image|max:2048',
        ]);

        $contributionType = ContributionType::findOrFail($validated['contribution_type_id']);

        if (!$contributionType->wallet_id) {
            // Allow admin/treasurer to choose wallet for bulk when type not linked
            if (!(in_array(auth()->user()->role, ['admin', 'bendahara']) && $request->wallet_id)) {
                return redirect()->back()->with('error', 'Jenis iuran ini belum memiliki dompet tujuan. Silakan pilih dompet atau hubungi administrator.');
            }
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $periods = $validated['periods'] ?? [$validated['payment_period'] ?? null];

        DB::transaction(function () use ($validated, $contributionType, $receiptPath, $periods, $userRole) {
            foreach ($validated['member_ids'] as $memberId) {
                foreach ($periods as $period) {
                    if ($period !== null) {
                        $alreadyPaid = Contribution::where('member_id', $memberId)
                            ->where('contribution_type_id', $validated['contribution_type_id'])
                            ->where('payment_period', $period)
                            ->where('status', 'paid')
                            ->exists();
                        if ($alreadyPaid) {
                            continue;
                        }
                    }
                    Contribution::create([
                        'member_id' => $memberId,
                        'contribution_type_id' => $validated['contribution_type_id'],
                        'amount' => $validated['amount'],
                        'payment_date' => $validated['payment_date'],
                        'payment_period' => $period,
                        'payment_method' => in_array($userRole, ['admin', 'bendahara']) ? ($validated['payment_method'] ?? 'cash') : 'transfer',
                        'notes' => $validated['notes'] ?? null,
                        'receipt_path' => $receiptPath,
                        'wallet_id' => $contributionType->wallet_id ?? $validated['wallet_id'],
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Pembayaran iuran massal berhasil dicatat untuk anggota terpilih. Menunggu verifikasi.');
    }

    public function periodSummary(Request $request)
    {
        $request->validate([
            'contribution_type_id' => 'required|exists:contribution_types,id',
            'payment_period' => 'required|string',
        ]);

        $type = ContributionType::findOrFail($request->input('contribution_type_id'));
        if (!$type->period || $type->period === 'once') {
            return response()->json([
                'message' => 'Jenis iuran ini tidak bersifat periodik.',
            ], 422);
        }

        $period = $request->input('payment_period');

        $activeMembers = Member::active()->get(['id', 'full_name', 'member_code']);
        $activeCount = $activeMembers->count();

        $paidContributions = Contribution::where('contribution_type_id', $type->id)
            ->where('status', 'paid')
            ->where('payment_period', $period)
            ->get(['member_id', 'amount']);

        $paidMemberIds = $paidContributions->pluck('member_id')->unique()->values()->toArray();
        $paidCount = count($paidMemberIds);
        $collectedAmount = (float) $paidContributions->sum('amount');
        $expectedAmount = (float) ($type->amount * $activeCount);
        $percentage = $activeCount > 0 ? round(($paidCount / $activeCount) * 100, 2) : 0.0;

        $paidMembers = Member::active()
            ->whereIn('id', $paidMemberIds)
            ->get(['id', 'full_name', 'member_code']);

        $unpaidMembers = Member::active()
            ->whereNotIn('id', $paidMemberIds)
            ->get(['id', 'full_name', 'member_code']);

        return response()->json([
            'type' => [
                'id' => $type->id,
                'name' => $type->name,
                'period' => $type->period,
                'amount' => (float) $type->amount,
            ],
            'period' => $period,
            'active_count' => $activeCount,
            'paid_count' => $paidCount,
            'unpaid_count' => max($activeCount - $paidCount, 0),
            'collected_amount' => $collectedAmount,
            'expected_amount' => $expectedAmount,
            'percentage' => $percentage,
            'paid_members' => $paidMembers,
            'unpaid_members' => $unpaidMembers,
        ]);
    }

    private function calculateTypeProgress(Member $member, ContributionType $type)
    {
        // ... (existing code) ...
        $query = Contribution::where('member_id', $member->id)
                             ->where('contribution_type_id', $type->id)
                             ->where('status', '!=', 'rejected');
        
        $contributions = $query->get();

        if ($type->period === 'once') {
             $paid = $contributions->where('status', 'paid')->isNotEmpty();
             return [
                 'paid' => $paid ? 1 : 0,
                 'total' => 1,
                 'percentage' => $paid ? 100 : 0,
                 'text' => $paid ? 'Lunas' : 'Belum Bayar'
             ];
        }

        // Periodic
        $startDate = $type->start_date 
            ? Carbon::parse($type->start_date) 
            : Carbon::parse($type->created_at)->startOfMonth();
        
        if ($type->end_date) {
            $endDate = Carbon::parse($type->end_date);
        } else {
             $endDate = Carbon::now();
             if ($type->period === 'monthly') $endDate->endOfMonth();
             if ($type->period === 'yearly') $endDate->endOfYear();
             if ($type->period === 'weekly') $endDate->endOfWeek();
        }

        $paidMap = $contributions->where('status', 'paid')->keyBy('payment_period');
        
        $totalPeriods = 0;
        $paidPeriods = 0;
        $current = $startDate->copy();
        
        $maxIterations = 500; // Safety
        
        while ($current <= $endDate && $totalPeriods < $maxIterations) {
            $key = '';
            
            if ($type->period === 'monthly') {
                $key = $current->format('Y-m');
            } elseif ($type->period === 'weekly') {
                $key = $current->format('o-W');
            } elseif ($type->period === 'yearly') {
                $key = $current->format('Y');
            } else {
                 $totalPeriods++;
                 $current->addDay();
                 continue; 
            }

            if ($paidMap->has($key)) {
                $paidPeriods++;
            }
            
            $totalPeriods++;
            
            if ($type->period === 'monthly') $current->addMonth();
            elseif ($type->period === 'weekly') $current->addWeek();
            elseif ($type->period === 'yearly') $current->addYear();
            else $current->addDay();
        }

        $percentage = $totalPeriods > 0 ? round(($paidPeriods / $totalPeriods) * 100) : 0;
        
        return [
            'paid' => $paidPeriods,
            'total' => $totalPeriods,
            'percentage' => $percentage,
            'text' => "$paidPeriods / $totalPeriods Periode",
        ];
    }
    
    public function monitoringList()
    {
        $totalActiveMembers = Member::where('status', 'active')->count(); // Ensure 'active' scope or column exists. If not, just count().
        // If status column doesn't exist on Member, use checks on User?
        // Let's assume Member has status or use count() for now as safer bet if status is standard.
        // Actually, previous code `Member::active()` suggests a scope exists.
        
        $types = ContributionType::where('is_active', true)
            ->withCount([
                'contributions as paid_count' => function ($query) {
                    $query->where('status', 'paid');
                },
                'contributions as pending_count' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->withSum([
                'contributions as collected_amount' => function ($query) {
                    $query->where('status', 'paid');
                }
            ], 'amount')
            ->get();

        // Calculate specific "Current Period" stats
        $types->transform(function ($type) use ($totalActiveMembers) {
            $currentPeriodPaidCount = 0;
            $targetCount = $totalActiveMembers;
            $currentPeriodLabel = 'Periode Ini';

            if ($type->period === 'monthly') {
                // Assume 2025-01 format based on getMemberStatus usage
                $currentPeriodKey = Carbon::now()->format('Y-m'); 
                $currentPeriodLabel = Carbon::now()->locale('id')->isoFormat('MMMM Y');
                
                // Try finding by Y-m first (standard)
                $currentPeriodPaidCount = $type->contributions()
                    ->where('status', 'paid')
                    ->where(function($q) use ($currentPeriodKey, $currentPeriodLabel) {
                        $q->where('payment_period', $currentPeriodKey)
                          ->orWhere('payment_period', $currentPeriodLabel); // Fallback if stored as label
                    })
                    ->count();

            } elseif ($type->period === 'yearly') {
                $currentPeriodKey = Carbon::now()->format('Y');
                $currentPeriodLabel = $currentPeriodKey;
                
                $currentPeriodPaidCount = $type->contributions()
                    ->where('status', 'paid')
                    ->where('payment_period', $currentPeriodKey)
                    ->count();

            } elseif ($type->period === 'weekly') {
                 $currentPeriodKey = Carbon::now()->format('o-W');
                 $currentPeriodLabel = 'Minggu ini';
                 $currentPeriodPaidCount = $type->contributions()
                    ->where('status', 'paid')
                    ->where('payment_period', $currentPeriodKey)
                    ->count();
            } else {
                // One time
                $currentPeriodLabel = 'Total';
                $currentPeriodPaidCount = $type->paid_count; // Cumulative
            }

            $type->current_period_paid = $currentPeriodPaidCount;
            $type->target_count = $targetCount;
            $type->current_period_label = $currentPeriodLabel;

            return $type;
        });
        
        return Inertia::render('Contributions/Monitoring/Index', [
            'types' => $types,
        ]);
    }

    public function monitoringDashboard(Request $request, ContributionType $contributionType)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfYear();

        // Base Query
        $baseQuery = Contribution::where('contribution_type_id', $contributionType->id)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        // Stats
        $stats = [
            'total_collected' => (clone $baseQuery)->where('status', 'paid')->sum('amount'),
            'total_pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'total_transactions' => (clone $baseQuery)->count(),
        ];
        
        // Chart: Monthly Trend
        $monthlyData = (clone $baseQuery)
            ->where('status', 'paid')
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'label' => Carbon::parse($item->month)->translatedFormat('M Y'),
                    'value' => (float)$item->total
                ];
            });

        // Calculate Member Status (Current Period Snapshot)
        $totalMembers = Member::where('status', 'active')->count();
        
        $referenceDate = $request->input('period_filter') 
            ? Carbon::parse($request->input('period_filter')) 
            : Carbon::now();
        
        $currentPeriodKey = null;
        $prevPeriodKey = null;
        $isPeriodic = true;
        
        if ($contributionType->period === 'monthly') {
            $currentPeriodKey = $referenceDate->format('Y-m');
            $prevPeriodKey = $referenceDate->copy()->subMonth()->format('Y-m');
        } elseif ($contributionType->period === 'yearly') {
            $currentPeriodKey = $referenceDate->format('Y');
            $prevPeriodKey = $referenceDate->copy()->subYear()->format('Y');
        } elseif ($contributionType->period === 'weekly') {
            $currentPeriodKey = $referenceDate->format('o-W');
            $prevPeriodKey = $referenceDate->copy()->subWeek()->format('o-W');
        } else {
            $isPeriodic = false;
        }

        $paidCurrent = 0;
        $paidPrevious = 0;

        if ($isPeriodic) {
             $paidCurrent = Contribution::where('contribution_type_id', $contributionType->id)
                ->where('status', 'paid')
                ->where(function($q) use ($currentPeriodKey) {
                    $q->where('payment_period', $currentPeriodKey)
                      ->orWhere('payment_period', 'like', "%$currentPeriodKey%"); // Relaxed check
                })
                ->count();
                
             $paidPrevious = Contribution::where('contribution_type_id', $contributionType->id)
                ->where('status', 'paid')
                 ->where(function($q) use ($prevPeriodKey) {
                    $q->where('payment_period', $prevPeriodKey)
                      ->orWhere('payment_period', 'like', "%$prevPeriodKey%");
                })
                ->count();
        } else {
             // For One-Time
             $paidCurrent = Contribution::where('contribution_type_id', $contributionType->id)
                ->where('status', 'paid')
                ->distinct('member_id')
                ->count('member_id');
             $paidPrevious = $paidCurrent; // Assume no arrears for one-time context
        }

        // Logic Implementation
        if (!$isPeriodic) {
            $lunas = $paidCurrent;
            $tunggak = 0; 
            $belum = max($totalMembers - $lunas, 0);
        } else {
            $lunas = $paidCurrent;
            // Arrears = Active members who paid neither current nor previous?
            // Simplified logic requested:
            // Tunggak: Missed Previous Period.
            $tunggak = max($totalMembers - $paidPrevious, 0);
            
            // Belum: Not Paid Current (but assuming history is OK, i.e. not in Tunggak count for dashboard simplicity, or we separate them rigidly).
            // Let's separate rigidly:
            // Lunas: Paid Current.
            // Tunggak: Missed Previous.
            // Belum (Just This Month): (Total - Lunas) - Tunggak?
            // If someone is Lunas, they are not Belum or Tunggak.
            // If someone missed Previous, they are Tunggak.
            // If someone paid Previous but missed Current, they are Belum.
            
            // Wait, if someone Paid Current, they count as Lunas.
            // But if they missed Previous, are they Lunas? They paid *current*.
            // Let's assume Lunas is strictly "Status of Current Period".
            
            // So:
            // Lunas = Paid Current.
            // Potential Problematic = Total - Lunas.
            // From Potential Problematic, how many are Tunggak?
            // Tunggak = (Total - Paid Previous).
            // But Paid Previous might overlap with Lunas.
            // Let's stick to simple aggregates:
            // Tunggak = Active Members who did NOT pay Previous.
            // Belum = (Total - Lunas) - Tunggak.
            // Safety clamp:
            $belum = max($totalMembers - $lunas - $tunggak, 0);
        }

        $memberStatus = [
            'paid' => $lunas,
            'unpaid' => $belum,
            'arrears' => $tunggak,
        ];
        
        $statusDistribution = [
            ['label' => 'Lunas', 'value' => $lunas, 'color' => '#16a34a'],
            ['label' => 'Belum Bayar', 'value' => $belum, 'color' => '#9ca3af'],
            ['label' => 'Menunggak', 'value' => $tunggak, 'color' => '#dc2626'],
        ];

        return Inertia::render('Contributions/Monitoring/Show', [
            'type' => $contributionType,
            'stats' => array_merge($stats, ['member_status' => $memberStatus]),
            'charts' => [
                'monthly' => $monthlyData,
                'status_distribution' => $statusDistribution,
            ],
             'filters' => [
                 'start_date' => $request->input('start_date') ? $startDate->format('Y-m-d') : null,
                 'end_date' => $request->input('end_date') ? $endDate->format('Y-m-d') : null,
                 'period_filter' => $request->input('period_filter'),
            ],
        ]);
    }

    
    public function verifyAction(Request $request, $id)
    {
        $contribution = Contribution::findOrFail($id);
        $action = $request->input('action'); // 'approve' or 'reject'

        $status = match ($action) {
            'approve' => 'paid',
            'reject' => 'rejected',
            default => null,
        };

        if (!$status) {
            return redirect()->back()->with('error', 'Aksi tidak valid');
        }

        DB::transaction(function () use ($contribution, $status) {
            $contribution->update([
                'status' => $status,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            if ($status === 'paid' && $contribution->wallet) {
                $contribution->wallet->increment('balance', $contribution->amount);
            }
        });

        return redirect()->back()->with('success', 'Transaksi berhasil diverifikasi.');
    }

    public function verification(Request $request)
    {
        // Only Pending
        $query = Contribution::with(['member', 'type', 'wallet'])
            ->where('status', 'pending')
            ->latest();
            
        if ($request->filled('contribution_type_id')) {
            $query->where('contribution_type_id', $request->input('contribution_type_id'));
        }
            
        return Inertia::render('Contributions/Monitoring/Verification', [
            'pendingTransactions' => $query->paginate(15)->withQueryString(),
            'types' => ContributionType::where('is_active', true)->get(),
        ]);
    }
    
    public function monitoringMatrix(Request $request, ContributionType $contributionType)
    {
        $year = $request->input('year', date('Y'));
        $years = range(date('Y'), date('Y') - 4);
        $members = Member::active()->orderBy('full_name')->get();
        
        // Generate Periods with Labels
        $periods = [];
        if ($contributionType->period === 'monthly') {
            for ($m = 1; $m <= 12; $m++) {
                $date = Carbon::createFromDate($year, $m, 1);
                $periods[] = [
                    'key' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('M'), 
                ];
            }
        } elseif ($contributionType->period === 'yearly') {
            $periods[] = [
                'key' => (string)$year,
                'label' => (string)$year
            ];
        } elseif ($contributionType->period === 'weekly') {
            $dto = new Carbon();
            $dto->setISODate($year, 53);
            $weeksInYear = ($dto->format('W') === '53' ? 53 : 52);
            for ($w = 1; $w <= $weeksInYear; $w++) {
                $periods[] = [
                    'key' => sprintf('%s-%02d', $year, $w),
                    'label' => 'W'.$w,
                ];
            }
        } else {
            $periods[] = [
                'key' => 'once',
                'label' => 'Status'
            ];
        }

        // Fetch contributions
        $contributions = Contribution::where('contribution_type_id', $contributionType->id)
            ->whereIn('status', ['paid', 'pending', 'partial'])
            ->when($contributionType->period !== 'once', function($q) use ($year) {
               $q->where('payment_period', 'like', "$year%"); 
            })
            ->get()
            ->groupBy('member_id');
            
        $matrixMembers = $members->map(function($member) use ($contributions, $periods, $contributionType) {
            $memberContribs = $contributions->get($member->id, collect());
            $statusByPeriod = [];
            
            foreach ($periods as $p) {
                $key = $p['key'];
                
                if ($contributionType->period === 'once') {
                     $c = $memberContribs->first();
                } else {
                     $c = $memberContribs->firstWhere('payment_period', $key);
                }

                $status = 'unpaid';
                if ($c) {
                    $status = $c->status;
                }
                
                $statusByPeriod[$key] = $status;
            }
            
            return [
                'id' => $member->id,
                'name' => $member->full_name,
                'member_code' => $member->member_code,
                'status_by_period' => $statusByPeriod
            ];
        });

        return Inertia::render('Contributions/Monitoring/Matrix', [
            'type' => $contributionType,
            'years' => $years,
            'filters' => ['year' => $year],
            'matrixData' => [
                'periods' => $periods,
                'members' => $matrixMembers,
            ],
        ]);
    }
    
    public function monitoringHistory(Request $request, ContributionType $contributionType) {
        $query = Contribution::with(['member'])
            ->where('contribution_type_id', $contributionType->id)
            ->latest();

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->whereHas('member', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%");
            });
        }
        
        return Inertia::render('Contributions/Index', [
             'contributions' => $query->paginate(15)->withQueryString(),
             'filters' => $request->only(['search']),
             'context' => 'admin-history', // Tell Index.vue to act as admin history
             'type' => $contributionType,
        ]);
    }
    public function exportMatrix(Request $request)
    {
        $types = ContributionType::where('is_active', true)->get();
        // Default to first type if exists or query param
        $selectedTypeId = $request->input('type_id') ?? ($types->first()->id ?? null);
        $year = $request->input('year', date('Y'));

        if (!$selectedTypeId) {
            return redirect()->back()->with('error', 'Tidak ada jenis iuran yang dipilih.');
        }

        $type = ContributionType::findOrFail($selectedTypeId);
        
        $fileName = sprintf('Matrix-Iuran-%s-%s.xlsx', \Str::slug($type->name), $year);

        return Excel::download(new ContributionMatrixExport($type, $year), $fileName);
    }
}
