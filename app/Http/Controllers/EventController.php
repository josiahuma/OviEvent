<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


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
        // ---- query inputs ----
        $q         = trim($request->query('q', ''));     // <— search text
        $category  = $request->query('category');
        $price     = $request->query('price', 'all');    // all|free|paid
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // ---- base query with eager loads + min/max session dates (for ordering) ----
        $base = Event::query()
            ->with(['sessions' => function ($q) {
                $q->orderBy('session_date', 'asc');
            }])
            ->withMin('sessions', 'session_date')
            ->withMax('sessions', 'session_date');

        // Category options for filters
        $categories = Event::whereNotNull('category')
            ->select('category')->distinct()->orderBy('category')->pluck('category');

        // ---- text search (name/organizer/location/category/description/tags) ----
        if ($q !== '') {
            $like = '%' . $q . '%';
            $base->where(function ($s) use ($like) {
                $s->where('name', 'like', $like)
                ->orWhere('organizer', 'like', $like)
                ->orWhere('location', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('description', 'like', $like)
                // tags is JSON/string in DB; LIKE still works well for quick search
                ->orWhere('tags', 'like', $like);
            });
        }

        // ---- filters ----
        if ($category) {
            $base->where('category', $category);
        }

        if ($price === 'free') {
            $base->where(function ($q) {
                $q->whereNull('ticket_cost')->orWhere('ticket_cost', 0);
            });
        } elseif ($price === 'paid') {
            $base->where('ticket_cost', '>', 0);
        }

        if ($startDate) {
            $base->whereHas('sessions', function ($q) use ($startDate) {
                $q->whereDate('session_date', '>=', $startDate);
            });
        }
        if ($endDate) {
            $base->whereHas('sessions', function ($q) use ($endDate) {
                $q->whereDate('session_date', '<=', $endDate);
            });
        }

        $now = Carbon::now();

        // ---------- Featured (promoted OR free) + has a future session ----------
        $featuredIds = (clone $base)
            ->where(function ($q) {
                $q->where('is_promoted', true)
                ->orWhere(function ($q2) {
                    $q2->whereNull('ticket_cost')->orWhere('ticket_cost', 0);
                });
            })
            ->whereHas('sessions', function ($q) use ($now) {
                $q->where('session_date', '>=', $now);
            })
            ->pluck('id');

        $featured = (clone $base)
            ->whereIn('id', $featuredIds)
            ->orderBy('sessions_min_session_date', 'asc')
            ->paginate(8, ['*'], 'featured_page');

        // ---------- Upcoming (future sessions) and NOT featured ----------
        $upcoming = (clone $base)
            ->whereHas('sessions', function ($q) use ($now) {
                $q->where('session_date', '>=', $now);
            })
            ->whereNotIn('id', $featuredIds)
            ->orderBy('sessions_min_session_date', 'asc')
            ->paginate(12, ['*'], 'upcoming_page');

        // ---------- Past (no future sessions; at least one past session) ----------
        $past = (clone $base)
            ->whereDoesntHave('sessions', function ($q) use ($now) {
                $q->where('session_date', '>=', $now);
            })
            ->whereHas('sessions', function ($q) use ($now) {
                $q->where('session_date', '<', $now);
            })
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
            ->withCount('registrations')
            ->withMin('sessions', 'session_date')
            ->with(['unlocks' => function ($q) {
                $q->where('user_id', Auth::id());
            }])
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
            'name' => 'required|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array', // <-- Expect an array now
            'tags.*' => 'string|max:50', // <-- Validate each tag
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'ticket_cost' => 'nullable|numeric|min:0',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'sessions.*.name' => 'required|string|max:255',
            'sessions.*.date' => 'required|date',
            'sessions.*.time' => 'required',
        ]);

        // Upload avatar
        $avatarUrl = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        // Upload banner
        $bannerUrl = $request->hasFile('banner')
            ? $request->file('banner')->store('banners', 'public')
            : null;


        // Handle tags
        // ✅ FIX: Safely handle tags from Tom Select
        $tagsArray = isset($validated['tags']) && is_array($validated['tags']) 
            ? $validated['tags'] 
            : [];

        // Create event
        $event = Event::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'organizer' => $validated['organizer'] ?? null,
            'category' => $validated['category'] ?? null,
            'tags' => json_encode($tagsArray),
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'ticket_cost' => $validated['ticket_cost'] ?? 0,
            'avatar_url' => $avatarUrl,
            'banner_url' => $bannerUrl,
        ]);

        // Store sessions
        if ($request->has('sessions')) {
            foreach ($request->sessions as $session) {
                $event->sessions()->create([
                    'session_name' => $session['name'],
                    'session_date' => $session['date'] . ' ' . $session['time'],
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Event created successfully!');
    }

    public function show($id)
    {
        $event = Event::with(['sessions' => function ($q) {
            $q->orderBy('session_date', 'asc');
        }])->findOrFail($id);

        return view('events.show', compact('event'));
    }

    public function avatar($id)
    {
        $event = Event::findOrFail($id);

        if (!$event->avatar_url) {
            return redirect()
                ->route('events.show', $id)
                ->with('error', 'This event does not have an avatar image yet.');
        }

        return view('events.avatar', compact('event'));
    }


     // -------- NEW: Edit / Update / Delete --------

    public function edit(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        return view('events.edit', compact('event'));
    }


    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'organizer'   => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:100',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'ticket_cost' => 'nullable|numeric|min:0',
            'avatar'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner'      => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            // Sessions editing can be added later on a dedicated page
        ]);

        // Upload avatar (replace if provided)
        if ($request->hasFile('avatar')) {
            if ($event->avatar_url) {
                Storage::disk('public')->delete($event->avatar_url);
            }
            $event->avatar_url = $request->file('avatar')->store('avatars', 'public');
        }

        // Upload banner (replace if provided)
        if ($request->hasFile('banner')) {
            if ($event->banner_url) {
                Storage::disk('public')->delete($event->banner_url);
            }
            $event->banner_url = $request->file('banner')->store('banners', 'public');
        }

        // Tags
        $tagsArray = isset($validated['tags']) && is_array($validated['tags'])
            ? $validated['tags']
            : [];

        // Update fields
        $event->name        = $validated['name'];
        $event->organizer   = $validated['organizer'] ?? null;
        $event->category    = $validated['category'] ?? null;
        $event->tags        = json_encode($tagsArray);
        $event->location    = $validated['location'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->ticket_cost = $validated['ticket_cost'] ?? 0;

        $event->save();

        return redirect()->route('dashboard')->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            abort(403);
        }

        // delete images
        if ($event->avatar_url) Storage::disk('public')->delete($event->avatar_url);
        if ($event->banner_url) Storage::disk('public')->delete($event->banner_url);

        $event->delete();

        return redirect()->route('dashboard')->with('success', 'Event deleted.');
    }
}

