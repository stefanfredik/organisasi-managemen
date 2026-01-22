<?php

namespace App\Http\Controllers;

use App\Models\ContributionType;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContributionTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('ContributionTypes/Index', [
            'types' => ContributionType::with('wallet')->get(),
            'wallets' => Wallet::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:once,daily,weekly,monthly,yearly',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'recurring_day' => 'nullable|integer|min:1|max:31',
        ]);

        ContributionType::create($validated);

        return redirect()->back()->with('success', 'Jenis iuran berhasil dibuat.');
    }

    public function update(Request $request, ContributionType $contributionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:once,daily,weekly,monthly,yearly',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'recurring_day' => 'nullable|integer|min:1|max:31',
        ]);

        $contributionType->update($validated);

        return redirect()->back()->with('success', 'Jenis iuran berhasil diperbarui.');
    }

    public function destroy(ContributionType $contributionType)
    {
        if ($contributionType->contributions()->count() > 0) {
            return redirect()->back()->with('error', 'Jenis iuran yang sudah memiliki data pembayaran tidak bisa dihapus.');
        }

        $contributionType->delete();

        return redirect()->back()->with('success', 'Jenis iuran berhasil dihapus.');
    }
}
