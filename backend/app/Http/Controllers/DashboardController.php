<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Event;
use App\Models\Finance;
use App\Models\Wallet;
use App\Models\Contribution;
use App\Models\Donation;
use App\Models\Announcement;
use App\Models\MeetingMinute;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get statistics based on user role
        $stats = $this->getStatistics($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($request);
        
        // Get upcoming events
        $upcomingEvents = Event::query()
            ->where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();
        
        // Get recent announcements
        $recentAnnouncements = Announcement::query()
            ->where('status', 'published')
            ->where('publish_date', '<=', now())
            ->latest('publish_date')
            ->limit(5)
            ->get();
        
        // Get financial summary (for admin, ketua, bendahara)
        $financialSummary = null;
        if (in_array($user->role, ['admin', 'ketua', 'bendahara'])) {
            $financialSummary = $this->getFinancialSummary();
        }
        
        // Get member's personal data (for anggota)
        $personalData = null;
        if ($user->role === 'anggota' && $user->member) {
            $personalData = $this->getPersonalData($user->member);
        }
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'upcomingEvents' => $upcomingEvents,
            'recentAnnouncements' => $recentAnnouncements,
            'financialSummary' => $financialSummary,
            'personalData' => $personalData,
        ]);
    }
    
    private function getStatistics($user)
    {
        $stats = [];
        
        // Common stats for all roles
        $stats['totalMembers'] = Member::count();
        $stats['activeMembers'] = Member::where('status', 'active')->count();
        $stats['upcomingEvents'] = Event::where('status', 'published')
            ->where('start_date', '>=', now())
            ->count();
        
        // Role-specific stats
        if (in_array($user->role, ['admin', 'ketua', 'bendahara'])) {
            $wallet = Wallet::first();
            $stats['totalBalance'] = $wallet ? $wallet->balance : 0;
            
            $stats['monthlyIncome'] = Finance::where('type', 'in')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');
            
            $stats['monthlyExpense'] = Finance::where('type', 'out')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');
            
            $stats['pendingContributions'] = Contribution::where('status', 'pending')->count();
            $stats['activeDonations'] = Donation::where('status', 'active')->count();
        }
        
        if (in_array($user->role, ['admin', 'ketua', 'sekretaris'])) {
            $stats['totalMeetings'] = MeetingMinute::count();
            $stats['totalAnnouncements'] = Announcement::where('status', 'published')->count();
        }
        
        return $stats;
    }
    
    
    private function getRecentActivities(Request $request)
    {
        // Get recent activity logs with pagination
        return \App\Models\ActivityLog::query()
            ->with('user')
            ->latest()
            ->paginate(10)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user ? $log->user->name : 'System',
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $log->created_at->diffForHumans(),
                ];
            });
    }
    
    private function getFinancialSummary()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Monthly income/expense for the last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $income = Finance::where('type', 'in')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
            
            $expense = Finance::where('type', 'out')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }
        
        return [
            'monthlyData' => $monthlyData,
            'totalIncome' => Finance::where('type', 'in')->sum('amount'),
            'totalExpense' => Finance::where('type', 'out')->sum('amount'),
        ];
    }
    
    private function getPersonalData($member)
    {
        // Get member's contributions
        $contributions = Contribution::where('member_id', $member->id)
            ->with('contributionType')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get member's event participations
        $eventParticipations = \App\Models\EventParticipant::where('member_id', $member->id)
            ->with('event')
            ->latest()
            ->limit(5)
            ->get();
        
        return [
            'contributions' => $contributions,
            'eventParticipations' => $eventParticipations,
            'totalContributions' => Contribution::where('member_id', $member->id)
                ->where('status', 'paid')
                ->sum('amount'),
            'pendingContributions' => Contribution::where('member_id', $member->id)
                ->where('status', 'pending')
                ->count(),
        ];
    }
}
