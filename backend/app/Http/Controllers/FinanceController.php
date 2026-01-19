<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        return Inertia::render('Finances/Index', [
            'finances' => Finance::with(['wallet', 'creator'])->latest()->paginate(10),
            'wallets' => Wallet::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|image|max:2048',
        ]);

        $validated['created_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        DB::transaction(function () use ($validated) {
            Finance::create($validated);

            $wallet = Wallet::find($validated['wallet_id']);
            if ($validated['type'] === 'in') {
                $wallet->increment('balance', $validated['amount']);
            } else {
                $wallet->decrement('balance', $validated['amount']);
            }
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dicatat.');
    }

    public function destroy(Finance $finance)
    {
        DB::transaction(function () use ($finance) {
            $wallet = $finance->wallet;
            if ($finance->type === 'in') {
                $wallet->decrement('balance', $finance->amount);
            } else {
                $wallet->increment('balance', $finance->amount);
            }
            $finance->delete();
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }
}
