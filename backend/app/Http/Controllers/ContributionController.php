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
        $query = Contribution::with(['member', 'type', 'wallet', 'verifier']);

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

        if (!$contributionTypeId) {
            return response()->json([]);
        }

        // Get all active members
        $allMembers = Member::active()->get(['id', 'full_name', 'member_code']);

        // Get members who have already paid this contribution type
        $paidMemberIds = Contribution::where('contribution_type_id', $contributionTypeId)
            ->where('status', 'paid')
            ->pluck('member_id')
            ->toArray();

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
