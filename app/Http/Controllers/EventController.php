<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventCreatedMail;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('registrations')->latest()->paginate(10);
        return view('events.index', compact('events'));
    }

    // Public homepage showing all events
    public function publicIndex(Request $request)
    {
        $q         = trim($request->query('q', ''));
        $category  = $request->query('category');
        $price     = $request->query('price', 'all'); // all|free|paid
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // Base query + eager loads + min/max session dates (for ordering)
        $base = Event::query()
            ->with(['sessions' => fn($q) => $q->orderBy('session_date', 'asc')])
            ->withMin('sessions', 'session_date')
            ->withMax('sessions', 'session_date');

        // Category options
        $categories = Event::whereNotNull('category')
            ->select('category')->distinct()->orderBy('category')->pluck('category');

        // Text search
        if ($q !== '') {
            $like = '%' . $q . '%';
            $base->where(function ($s) use ($like) {
                $s->where('name', 'like', $like)
                  ->orWhere('organizer', 'like', $like)
                  ->orWhere('location', 'like', $like)
                  ->orWhere('category', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('tags', 'like', $like); // JSON/string search
            });
        }

        // Filters
        if ($category) {
            $base->where('category', $category);
        }

        if ($price === 'free') {
            $base->where(fn($q) => $q->whereNull('ticket_cost')->orWhere('ticket_cost', 0));
        } elseif ($price === 'paid') {
            $base->where('ticket_cost', '>', 0);
        }

        if ($startDate) {
            $base->whereHas('sessions', fn($q) => $q->whereDate('session_date', '>=', $startDate));
        }
        if ($endDate) {
            $base->whereHas('sessions', fn($q) => $q->whereDate('session_date', '<=', $endDate));
        }

        $now = Carbon::now();

        // Featured: ONLY promoted & with a future session
        $featuredIds = (clone $base)
            ->where('is_promoted', true)
            ->whereHas('sessions', fn($q) => $q->where('session_date', '>=', $now))
            ->pluck('id');

        $featured = (clone $base)
            ->whereIn('id', $featuredIds)
            ->orderBy('sessions_min_session_date', 'asc')
            ->paginate(8, ['*'], 'featured_page');

        // Upcoming: future sessions, EXCLUDING featured to avoid dupes
        $upcoming = (clone $base)
            ->whereHas('sessions', fn($q) => $q->where('session_date', '>=', $now))
            ->when($featuredIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $featuredIds))
            ->orderBy('sessions_min_session_date', 'asc')
            ->paginate(12, ['*'], 'upcoming_page');

        // Past: no future sessions; has at least one past session
        $past = (clone $base)
            ->whereDoesntHave('sessions', fn($q) => $q->where('session_date', '>=', $now))
            ->whereHas('sessions', fn($q) => $q->where('session_date', '<', $now))
            ->orderBy('sessions_max_session_date', 'desc')
            ->paginate(12, ['*'], 'past_page');

        return view('events.public-index', [
            'categories' => $categories,
            'category'   => $category,
            'price'      => $price,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'q'          => $q,
            'featured'   => $featured,
            'upcoming'   => $upcoming,
            'past'       => $past,
        ]);
    }

    // User dashboard showing their own events
    public function dashboard()
    {
        $events = Event::where('user_id', Auth::id())
            ->withCount([
                'registrations as registrations_count' => function ($q) {
                    $q->whereIn('status', ['paid','free']); // exclude 'pending'
                }
            ])
            ->withMin('sessions', 'session_date')
            ->with(['unlocks' => fn($q) => $q->where('user_id', Auth::id())])
            ->latest()
            ->paginate(12);

        return view('dashboard', compact('events'));
    }

    // Show event creation form
    public function create()
    {
        return view('events.create');
    }

    // Store event in database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'organizer'    => 'nullable|string|max:255',
            'category'     => 'nullable|string|max:100',
            'tags'         => 'nullable|array',
            'tags.*'       => 'string|max:50',
            'location'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'ticket_cost'     => 'nullable|numeric|min:0|max:99999999.99',
            'ticket_currency' => 'required|string|in:GBP,USD,EUR,NGN,KES,GHS,ZAR,CAD,AUD|size:3',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner'       => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'is_promoted'  => 'nullable|boolean',
            'sessions.*.name' => 'required|string|max:255',
            'sessions.*.date' => 'required|date',
            'sessions.*.time' => 'required',
        ]);

        $avatarUrl = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $bannerUrl = $request->hasFile('banner')
            ? $request->file('banner')->store('banners', 'public')
            : null;

        $tagsArray = isset($validated['tags']) && is_array($validated['tags']) ? $validated['tags'] : [];

        $event = Event::create([
            'user_id'     => Auth::id(),
            'name'        => $validated['name'],
            'organizer'   => $validated['organizer'] ?? null,
            'category'    => $validated['category'] ?? null,
            'tags'        => json_encode($tagsArray),
            'location'    => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'ticket_cost' => $validated['ticket_cost'] ?? 0,
            'ticket_currency' => strtoupper($validated['ticket_currency']),
            'avatar_url'  => $avatarUrl,
            'banner_url'  => $bannerUrl,
            'is_promoted' => $validated['is_promoted'] ?? false,
        ]);

        if ($request->has('sessions')) {
            foreach ($request->sessions as $session) {
                $event->sessions()->create([
                    'session_name' => $session['name'],
                    'session_date' => $session['date'] . ' ' . $session['time'],
                ]);
            }
        }

        Mail::to(auth()->user()->email)->send(new EventCreatedMail($event));

        return redirect()->route('dashboard')->with('success', 'Event created successfully!');
    }

    // PUBLIC show — uses implicit binding on {event} with your public_id fallback
    public function show(Event $event)
    {
        $event->load(['sessions' => fn($q) => $q->orderBy('session_date', 'asc')]);
        return view('events.show', compact('event'));
    }

    // PUBLIC avatar page — also uses implicit binding
    public function avatar(Event $event)
    {
        if (!$event->avatar_url) {
            return redirect()
                ->route('events.show', $event) // pass model so URL uses public_id
                ->with('error', 'This event does not have an avatar image yet.');
        }

        return view('events.avatar', compact('event'));
    }

    // -------- Edit / Update / Delete (organizer) --------

    public function edit(Event $event)
    {
        abort_if($event->user_id !== Auth::id(), 403);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        abort_if($event->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'organizer'   => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:100',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'ticket_cost'     => 'nullable|numeric|min:0|max:99999999.99',
            'ticket_currency' => 'required|string|in:GBP,USD,EUR,NGN,KES,GHS,ZAR,CAD,AUD|size:3',
            'avatar'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner'      => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Avatar
        if ($request->hasFile('avatar')) {
            if ($event->avatar_url) {
                Storage::disk('public')->delete($event->avatar_url);
            }
            $event->avatar_url = $request->file('avatar')->store('avatars', 'public');
        }

        // Banner
        if ($request->hasFile('banner')) {
            if ($event->banner_url) {
                Storage::disk('public')->delete($event->banner_url);
            }
            $event->banner_url = $request->file('banner')->store('banners', 'public');
        }

        $tagsArray = isset($validated['tags']) && is_array($validated['tags']) ? $validated['tags'] : [];

        $event->name        = $validated['name'];
        $event->organizer   = $validated['organizer'] ?? null;
        $event->category    = $validated['category'] ?? null;
        $event->tags        = json_encode($tagsArray);
        $event->location    = $validated['location'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->ticket_cost = $validated['ticket_cost'] ?? 0;
        $event->ticket_currency = strtoupper($validated['ticket_currency']);

        $event->save();

        // Session upsert/delete (optional UI provides payload)
        $sessions   = $request->input('sessions', []);
        $touchedIds = [];

        foreach ($sessions as $row) {
            $name   = trim($row['name'] ?? '');
            $date   = $row['date'] ?? null;
            $time   = $row['time'] ?? null;
            $delete = isset($row['_delete']) && (int)$row['_delete'] === 1;
            $id     = $row['id'] ?? null;

            $dt = ($date && $time) ? Carbon::parse("{$date} {$time}") : null;

            if ($id) {
                $session = EventSession::where('event_id', $event->id)->where('id', $id)->first();
                if (!$session) { continue; }

                if ($delete) { $session->delete(); continue; }

                $session->session_name = $name ?: $session->session_name;
                if ($dt) { $session->session_date = $dt; }
                $session->save();
                $touchedIds[] = $session->id;
            } else {
                if ($delete || !$name || !$dt) { continue; }
                $created = EventSession::create([
                    'event_id'     => $event->id,
                    'session_name' => $name,
                    'session_date' => $dt,
                ]);
                $touchedIds[] = $created->id;
            }
        }

        return redirect()->route('dashboard')->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        abort_if($event->user_id !== Auth::id(), 403);

        if ($event->avatar_url) Storage::disk('public')->delete($event->avatar_url);
        if ($event->banner_url) Storage::disk('public')->delete($event->banner_url);

        $event->delete();

        return redirect()->route('dashboard')->with('success', 'Event deleted.');
    }
}
