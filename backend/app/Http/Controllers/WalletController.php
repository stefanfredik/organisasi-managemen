<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index()
    {
        $total_balance = \App\Models\Wallet::sum('balance');
        $total_income = \App\Models\Finance::where('type', 'in')->sum('amount') + \App\Models\Contribution::where('status', 'paid')->sum('amount');
        $total_expense = \App\Models\Finance::where('type', 'out')->sum('amount');

        return Inertia::render('Wallets/Index', [
            'wallets' => Wallet::withCount(['finances', 'contributions'])->get(),
            'stats' => [
                'total_balance' => (float) $total_balance,
                'total_income' => (float) $total_income,
                'total_expense' => (float) $total_expense,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'balance' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        Wallet::create($validated);

        return redirect()->back()->with('success', 'Dompet berhasil dibuat.');
    }

    public function update(Request $request, Wallet $wallet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $wallet->update($validated);

        return redirect()->back()->with('success', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet)
    {
        if ($wallet->balance > 0) {
            return redirect()->back()->with('error', 'Dompet dengan saldo tidak bisa dihapus.');
        }

        $wallet->delete();

        return redirect()->back()->with('success', 'Dompet berhasil dihapus.');
    }
}
