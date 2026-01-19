<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;

class PublicEventController extends Controller
{
    public function index()
    {
        $events = Event::published()
            ->where('is_public', true)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        return Inertia::render('Public/Events/Index', [
            'events' => $events,
        ]);
    }

    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->where('is_public', true)
            ->with('documentations')
            ->firstOrFail();

        return Inertia::render('Public/Events/Show', [
            'event' => $event,
        ]);
    }
}
