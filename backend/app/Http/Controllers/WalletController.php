<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index()
    {
        return Inertia::render('Wallets/Index', [
            'wallets' => Wallet::withCount(['finances', 'contributions'])->get(),
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
