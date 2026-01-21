<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Wallet;
use App\Models\Contribution;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;
use App\Exports\CashFlowExport;
use App\Exports\ContributionsExport;
use App\Exports\DonationsExport;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function financial(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Get financial transactions
        $transactions = Finance::query()
            ->with(['wallet', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();
        
        // Calculate summary
        $totalIncome = $transactions->where('type', 'in')->sum('amount');
        $totalExpense = $transactions->where('type', 'out')->sum('amount');
        $netIncome = $totalIncome - $totalExpense;
        
        // Get wallet balance
        $wallet = Wallet::first();
        $currentBalance = $wallet ? $wallet->balance : 0;
        
        return Inertia::render('Reports/Financial', [
            'transactions' => $transactions,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netIncome' => $netIncome,
                'currentBalance' => $currentBalance,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function financialPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $transactions = Finance::query()
            ->with(['wallet', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();
        
        $totalIncome = $transactions->where('type', 'in')->sum('amount');
        $totalExpense = $transactions->where('type', 'out')->sum('amount');
        
        $wallet = Wallet::first();
        $currentBalance = $wallet ? $wallet->balance : 0;
        
        $pdf = Pdf::loadView('reports.financial_pdf', [
            'transactions' => $transactions,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netIncome' => $totalIncome - $totalExpense,
                'currentBalance' => $currentBalance,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
        
        return $pdf->download('Laporan-Keuangan-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    public function financialExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $transactions = Finance::query()
            ->with(['wallet', 'creator'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();
            
        return Excel::download(new FinancialReportExport($transactions), 'Laporan-Keuangan-' . $startDate . '-to-' . $endDate . '.xlsx');
    }

    public function cashFlow(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));
        
        // Get monthly cash flow data
        $monthlyData = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($start->lte($end)) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $income = Finance::where('type', 'in')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $expense = Finance::where('type', 'out')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $start->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
            
            $start->addMonth();
        }
        
        // Get category breakdown
        $incomeByCategory = Finance::where('type', 'in')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
        
        $expenseByCategory = Finance::where('type', 'out')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
        
        return Inertia::render('Reports/CashFlow', [
            'monthlyData' => $monthlyData,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function cashFlowPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));
        
        $monthlyData = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($start->lte($end)) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $income = Finance::where('type', 'in')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $expense = Finance::where('type', 'out')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $start->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
            $start->addMonth();
        }
        
        $pdf = Pdf::loadView('reports.cash_flow_pdf', [
            'monthlyData' => $monthlyData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
        
        return $pdf->download('Laporan-Arus-Kas-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    public function cashFlowExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));
        
        $monthlyData = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($start->lte($end)) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $income = Finance::where('type', 'in')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $expense = Finance::where('type', 'out')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $start->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
            $start->addMonth();
        }
            
        return Excel::download(new CashFlowExport($monthlyData), 'Laporan-Arus-Kas-' . $startDate . '-to-' . $endDate . '.xlsx');
    }


    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));
        
        // Get wallet balance
        $wallet = Wallet::first();
        $cashBalance = $wallet ? $wallet->balance : 0;
        
        // Get pending contributions (accounts receivable)
        $pendingContributions = Contribution::where('status', 'pending')
            ->where('payment_date', '<=', $asOfDate)
            ->sum('amount');
        
        // Get total assets
        $totalAssets = $cashBalance + $pendingContributions;
        
        // Get active donations (liabilities - commitments)
        $activeDonations = Donation::where('status', 'active')
            ->where('created_at', '<=', $asOfDate)
            ->get();
        
        $donationCommitments = $activeDonations->sum(function ($donation) {
            return $donation->target_amount - $donation->current_amount;
        });
        
        // Calculate equity
        $totalLiabilities = $donationCommitments;
        $equity = $totalAssets - $totalLiabilities;
        
        // Get historical data
        $totalIncome = Finance::where('type', 'in')
            ->where('transaction_date', '<=', $asOfDate)
            ->sum('amount');
        
        $totalExpense = Finance::where('type', 'out')
            ->where('transaction_date', '<=', $asOfDate)
            ->sum('amount');
        
        return Inertia::render('Reports/BalanceSheet', [
            'assets' => [
                'cash' => $cashBalance,
                'receivables' => $pendingContributions,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'donationCommitments' => $donationCommitments,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'retainedEarnings' => $equity,
                'total' => $equity,
            ],
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netIncome' => $totalIncome - $totalExpense,
            ],
            'filters' => [
                'as_of_date' => $asOfDate,
            ],
        ]);
    }

    public function balanceSheetPdf(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));
        
        $wallet = Wallet::first();
        $cashBalance = $wallet ? $wallet->balance : 0;
        
        $pendingContributions = Contribution::where('status', 'pending')
            ->where('payment_date', '<=', $asOfDate)
            ->sum('amount');
        
        $totalAssets = $cashBalance + $pendingContributions;
        
        $activeDonations = Donation::where('status', 'active')
            ->where('created_at', '<=', $asOfDate)
            ->get();
        
        $donationCommitments = $activeDonations->sum(function ($donation) {
            return $donation->target_amount - $donation->current_amount;
        });
        
        $totalLiabilities = $donationCommitments;
        $equity = $totalAssets - $totalLiabilities;
        
        $pdf = Pdf::loadView('reports.balance_sheet_pdf', [
            'assets' => [
                'cash' => $cashBalance,
                'receivables' => $pendingContributions,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'donationCommitments' => $donationCommitments,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'retainedEarnings' => $equity,
                'total' => $equity,
            ],
            'filters' => [
                'as_of_date' => $asOfDate,
            ],
        ]);
        
        return $pdf->download('Laporan-Neraca-' . $asOfDate . '.pdf');
    }


    public function contributions(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status');
        
        $query = Contribution::query()
            ->with(['member', 'type'])
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $contributions = $query->orderBy('payment_date', 'desc')->get();
        
        // Calculate summary
        $totalPaid = $contributions->where('status', 'paid')->sum('amount');
        $totalPending = $contributions->where('status', 'pending')->sum('amount');
        $totalRejected = $contributions->where('status', 'rejected')->sum('amount');
        
        return Inertia::render('Reports/Contributions', [
            'contributions' => $contributions,
            'summary' => [
                'totalPaid' => $totalPaid,
                'totalPending' => $totalPending,
                'totalRejected' => $totalRejected,
                'total' => $totalPaid + $totalPending + $totalRejected,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
            ],
        ]);
    }

    public function donations(Request $request)
    {
        $status = $request->input('status');
        
        $query = Donation::query()
            ->with(['transactions']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $donations = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary
        $totalTarget = $donations->sum('target_amount');
        $totalCollected = $donations->sum('collected_amount');
        $totalRemaining = $totalTarget - $totalCollected;
        
        return Inertia::render('Reports/Donations', [
            'donations' => $donations,
            'summary' => [
                'totalTarget' => $totalTarget,
                'totalCollected' => $totalCollected,
                'totalRemaining' => $totalRemaining,
                'completionRate' => $totalTarget > 0 ? ($totalCollected / $totalTarget) * 100 : 0,
            ],
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function contributionsPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status');
        
        $query = Contribution::query()
            ->with(['member', 'type'])
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $contributions = $query->orderBy('payment_date', 'asc')->get();
        
        $totalPaid = $contributions->where('status', 'paid')->sum('amount');
        $totalPending = $contributions->where('status', 'pending')->sum('amount');
        $totalRejected = $contributions->where('status', 'rejected')->sum('amount');
        
        $pdf = Pdf::loadView('reports.contributions_pdf', [
            'contributions' => $contributions,
            'summary' => [
                'totalPaid' => $totalPaid,
                'totalPending' => $totalPending,
                'totalRejected' => $totalRejected,
                'total' => $totalPaid + $totalPending + $totalRejected,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
        
        return $pdf->download('Laporan-Iuran-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    public function contributionsExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status');
        
        $query = Contribution::query()
            ->with(['member', 'type'])
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $contributions = $query->orderBy('payment_date', 'asc')->get();
            
        return Excel::download(new ContributionsExport($contributions), 'Laporan-Iuran-' . $startDate . '-to-' . $endDate . '.xlsx');
    }

    public function donationsPdf(Request $request)
    {
        $status = $request->input('status');
        
        $query = Donation::query()
            ->with(['transactions']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $donations = $query->orderBy('created_at', 'asc')->get();
        
        $totalTarget = $donations->sum('target_amount');
        $totalCollected = $donations->sum('collected_amount');
        
        $pdf = Pdf::loadView('reports.donations_pdf', [
            'donations' => $donations,
            'summary' => [
                'totalTarget' => $totalTarget,
                'totalCollected' => $totalCollected,
                'totalRemaining' => $totalTarget - $totalCollected,
                'completionRate' => $totalTarget > 0 ? ($totalCollected / $totalTarget) * 100 : 0,
            ],
            'filters' => [
                'status' => $status,
            ],
        ]);
        
        return $pdf->download('Laporan-Donasi.pdf');
    }

    public function donationsExcel(Request $request)
    {
        $status = $request->input('status');
        
        $query = Donation::query()
            ->with(['transactions']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $donations = $query->orderBy('created_at', 'asc')->get();
            
        return Excel::download(new DonationsExport($donations), 'Laporan-Donasi.xlsx');
    }
}

