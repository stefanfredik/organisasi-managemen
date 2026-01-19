<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Wallet::class, 'wallet');
    }
    public function index()
    {
        $total_balance = \App\Models\Wallet::sum('balance');
        $total_income = \App\Models\Finance::where('type', 'in')->sum('amount') + \App\Models\Contribution::where('status', 'paid')->sum('amount');
        $total_expense = \App\Models\Finance::where('type', 'out')->sum('amount');

        return Inertia::render('Wallets/Index', [
            'wallets' => Wallet::withCount(['finances', 'contributions'])
                ->withSum([
                    'finances as total_income' => function ($query) {
                        $query->where('type', 'in');
                    }
                ], 'amount')
                ->withSum([
                    'finances as total_expense' => function ($query) {
                        $query->where('type', 'out');
                    }
                ], 'amount')
                ->get(),
            'stats' => [
                'total_balance' => (float) $total_balance,
                'total_income' => (float) $total_income,
                'total_expense' => (float) $total_expense,
            ],
        ]);
    }

    public function show(Request $request, Wallet $wallet)
    {
        // Finances Query with Filters
        $financesQuery = $wallet->finances()->with('creator')->latest('transaction_date');

        if ($request->finance_start_date) {
            $financesQuery->whereDate('transaction_date', '>=', $request->finance_start_date);
        }
        if ($request->finance_end_date) {
            $financesQuery->whereDate('transaction_date', '<=', $request->finance_end_date);
        }
        if ($request->finance_type) {
            $financesQuery->where('type', $request->finance_type);
        }
        if ($request->finance_search) {
            $financesQuery->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->finance_search . '%')
                    ->orWhere('category', 'like', '%' . $request->finance_search . '%');
            });
        }

        $finances = $financesQuery->paginate(10, ['*'], 'finances_page')->withQueryString();

        // Contributions Query with Filters
        $contributionsQuery = $wallet->contributions()
            ->with(['member', 'type'])
            ->where('status', 'paid')
            ->latest('payment_date');

        if ($request->contrib_start_date) {
            $contributionsQuery->whereDate('payment_date', '>=', $request->contrib_start_date);
        }
        if ($request->contrib_end_date) {
            $contributionsQuery->whereDate('payment_date', '<=', $request->contrib_end_date);
        }
        if ($request->contrib_search) {
            $contributionsQuery->whereHas('member', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->contrib_search . '%')
                    ->orWhere('member_code', 'like', '%' . $request->contrib_search . '%');
            });
        }

        $contributions = $contributionsQuery->paginate(10, ['*'], 'contributions_page')->withQueryString();

        // Calculate totals dynamically for this specific wallet (unaffected by view filters for now, or should they be? usually stats on top are total total)
        // Keeping stats as "Total Wallet Stats" not "Filtered Stats" to avoid confusion unless requested.
        $wallet_income = $wallet->finances()->where('type', 'in')->sum('amount');
        $wallet_expense = $wallet->finances()->where('type', 'out')->sum('amount');
        $contribution_income = $wallet->contributions()->where('status', 'paid')->sum('amount');

        // Chart Data Filtering
        $chartYear = $request->input('chart_year', date('Y'));

        // Available years for filter dropdown
        $availableYears = $wallet->finances()
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        // Chart Data 1: Monthly Trend (Selected Year)
        // We show all 12 months of the selected year
        $monthly_stats = $wallet->finances()
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month, type, SUM(amount) as total")
            ->whereYear('transaction_date', $chartYear)
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $labels = [];
        $income_data = [];
        $expense_data = [];

        // Generate labels for Jan-Dec of selected year
        for ($m = 1; $m <= 12; $m++) {
            $date = \Carbon\Carbon::createFromDate($chartYear, $m, 1);
            $monthKey = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M'); // Jan, Feb, etc.

            $income_data[] = $monthly_stats->has($monthKey) ? $monthly_stats[$monthKey]->where('type', 'in')->sum('total') : 0;
            $expense_data[] = $monthly_stats->has($monthKey) ? $monthly_stats[$monthKey]->where('type', 'out')->sum('total') : 0;
        }

        // Chart Data 2: Expense Categories Breakdown (Selected Year)
        $category_stats = $wallet->finances()
            ->where('type', 'out')
            ->whereYear('transaction_date', $chartYear)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return Inertia::render('Wallets/Show', [
            'wallet' => $wallet,
            'finances' => $finances,
            'contributions' => $contributions,
            'stats' => [
                'total_income' => $wallet_income + $contribution_income,
                'total_expense' => $wallet_expense,
                'finance_income' => $wallet_income,
                'contribution_income' => $contribution_income,
            ],
            'chart_data' => [
                'trend' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Pemasukan',
                            'backgroundColor' => '#34D399', // green-400
                            'data' => $income_data
                        ],
                        [
                            'label' => 'Pengeluaran',
                            'backgroundColor' => '#F87171', // red-400
                            'data' => $expense_data
                        ]
                    ]
                ],
                'categories' => [
                    'labels' => $category_stats->pluck('category'),
                    'datasets' => [
                        [
                            'data' => $category_stats->pluck('total'),
                            'backgroundColor' => ['#6366F1', '#EC4899', '#F59E0B', '#10B981', '#3B82F6'],
                        ]
                    ]
                ]
            ],
            'available_years' => $availableYears,
            'filters' => array_merge($request->only([
                'finance_start_date',
                'finance_end_date',
                'finance_type',
                'finance_search',
                'contrib_start_date',
                'contrib_end_date',
                'contrib_search'
            ]), ['chart_year' => $chartYear]),
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
            return redirect()->back()->with('error', 'Gagal hapus: Dompet masih memiliki saldo (Rp ' . number_format((float) $wallet->balance, 0, ',', '.') . '). Silakan kosongkan terlebih dahulu.');
        }

        if ($wallet->finances()->exists() || $wallet->contributions()->exists()) {
            return redirect()->back()->with('error', 'Gagal hapus: Dompet ini sudah memiliki riwayat transaksi atau iuran. Data tidak boleh dihapus demi integritas laporan keuangan.');
        }

        $wallet->delete();

        return redirect()->back()->with('success', 'Dompet berhasil dihapus.');
    }
}
