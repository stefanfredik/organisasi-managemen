<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Donation;
use App\Models\Announcement;
use App\Models\VisionMission;
use App\Models\OrganizationStructure;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $upcomingEvents = Event::published()
            ->where('is_public', true)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        $latestAnnouncements = Announcement::published()
            ->where('target_audience', 'all')
            ->latest()
            ->limit(3)
            ->get();

        $activeDonations = Donation::where('status', 'active')
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('Public/Home', [
            'upcomingEvents' => $upcomingEvents,
            'latestAnnouncements' => $latestAnnouncements,
            'activeDonations' => $activeDonations,
        ]);
    }

    public function about()
    {
        return Inertia::render('Public/About');
    }

    public function visionMission()
    {
        $visionMission = VisionMission::active()->first();
        $history = VisionMission::latest()->get();

        return Inertia::render('Public/VisionMission', [
            'visionMission' => $visionMission,
            'history' => $history,
        ]);
    }

    public function structure()
    {
        $structures = OrganizationStructure::with(['member', 'parent', 'children'])
            ->get();

        return Inertia::render('Public/Structure', [
            'structures' => $structures,
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }
}
