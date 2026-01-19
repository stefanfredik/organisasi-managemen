<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Finance::class, 'finance');
    }
    public function index(Request $request)
    {
        $query = Finance::with(['wallet', 'creator']);

        // Filters
        $query->when($request->search, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        });

        $query->when($request->wallet_id, function ($q, $walletId) {
            $q->where('wallet_id', $walletId);
        });

        $query->when($request->type, function ($q, $type) {
            $q->where('type', $type);
        });

        $query->when($request->date_from, function ($q, $dateFrom) {
            $q->whereDate('transaction_date', '>=', $dateFrom);
        });

        $query->when($request->date_to, function ($q, $dateTo) {
            $q->whereDate('transaction_date', '<=', $dateTo);
        });

        return Inertia::render('Finances/Index', [
            'finances' => $query->latest('transaction_date')->latest('id')->paginate(10)->withQueryString(),
            'wallets' => Wallet::where('is_active', true)->get(),
            'filters' => $request->only(['search', 'wallet_id', 'type', 'date_from', 'date_to']),
            'stats' => [
                'total_income' => (float) (Finance::where('type', 'in')->sum('amount') + \App\Models\Contribution::where('status', 'paid')->sum('amount')),
                'total_expense' => (float) Finance::where('type', 'out')->sum('amount'),
            ],
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
