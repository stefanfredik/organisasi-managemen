<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Donation;
use Inertia\Inertia;

class PublicDonationController extends Controller
{
    /**
     * Display a listing of active and public donations.
     */
    public function index(Request $request)
    {
        $donations = Donation::where('status', 'active')
            ->where('is_public', true)
            ->when($request->search, function ($query, $search) {
                $query->where('program_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Donations/Index', [
            'donations' => $donations,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified donation for public view.
     */
    public function show($slug)
    {
        $donation = Donation::where('slug', $slug)
            ->where('is_public', true)
            ->with([
                'transactions' => function ($query) {
                    $query->latest()->take(10);
                }
            ])
            ->firstOrFail();

        return Inertia::render('Public/Donations/Show', [
            'donation' => $donation,
        ]);
    }
}
