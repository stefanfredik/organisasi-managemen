<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\ContributionType;
use App\Models\Member;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
    public function index()
    {
        $query = Contribution::with(['member' => function ($query) {
            $query->withTrashed();
        }, 'type', 'wallet', 'verifier']);

        if (auth()->user()->role === 'member') {
            $query->where('member_id', auth()->user()->member->id);
        }

        return Inertia::render('Contributions/Index', [
            'contributions' => $query->latest()->paginate(10),
            'types' => ContributionType::where('is_active', true)->get(),
            'wallets' => Wallet::where('is_active', true)->get(),
            'members' => Member::active()->get(['id', 'full_name', 'member_code']),
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
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'contribution_type_id' => 'required|exists:contribution_types,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_period' => 'nullable|string',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|image|max:2048',
        ]);

        // Get wallet from contribution type
        $contributionType = ContributionType::findOrFail($validated['contribution_type_id']);

        if (!$contributionType->wallet_id) {
            return redirect()->back()->with('error', 'Jenis iuran ini belum memiliki dompet tujuan. Silakan hubungi administrator.');
        }

        $validated['wallet_id'] = $contributionType->wallet_id;

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
}
