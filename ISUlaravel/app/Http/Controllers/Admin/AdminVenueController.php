<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Models\Venue;
use App\Services\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminVenueController extends Controller
{
    public function __construct(
        protected VenueService $venueService
    ) {
    }

    /**
     * Check if user is administrator
     */
    protected function ensureAdmin()
    {
        if (!auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Display a listing of venues
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();
        
        $query = Venue::withCount('reservations')->with('areas');
        
        // Search by name or location
        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }
        
        $venues = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.venues.index', compact('venues', 'search', 'status'));
    }

    /**
     * Show the form for creating a new venue
     */
    public function create()
    {
        $this->ensureAdmin();
        return view('admin.venues.create');
    }

    /**
     * Store a newly created venue
     */
    public function store(StoreVenueRequest $request)
    {
        $this->ensureAdmin();
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('venues', 'public');
        }

        Venue::create($data);

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue created successfully.');
    }

    /**
     * Display the specified venue
     */
    public function show(Venue $venue)
    {
        $this->ensureAdmin();
        $venue->load('reservations.user');

        return view('admin.venues.show', compact('venue'));
    }

    /**
     * Show the form for editing the specified venue
     */
    public function edit(Venue $venue)
    {
        $this->ensureAdmin();
        return view('admin.venues.edit', compact('venue'));
    }

    /**
     * Update the specified venue
     */
    public function update(StoreVenueRequest $request, Venue $venue)
    {
        $this->ensureAdmin();
        $data = $request->validated();

        // Handle photo upload/replacement
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($venue->photo && Storage::disk('public')->exists($venue->photo)) {
                Storage::disk('public')->delete($venue->photo);
            }
            // Store new photo
            $data['photo'] = $request->file('photo')->store('venues', 'public');
        } elseif ($request->has('remove_photo') && $request->remove_photo == '1') {
            // Remove photo if requested
            if ($venue->photo && Storage::disk('public')->exists($venue->photo)) {
                Storage::disk('public')->delete($venue->photo);
            }
            $data['photo'] = null;
        } else {
            // Keep existing photo
            unset($data['photo']);
        }

        // Check if status is being changed
        $statusChanged = isset($data['status']) && $venue->status !== $data['status'];
        $oldStatus = $venue->status;

        // Update status using VenueService to handle postponement
        if ($statusChanged && isset($data['status'])) {
            $result = $this->venueService->updateStatus(
                $venue,
                $data['status'],
                $data['unavailable_until'] ?? null
            );
            
            // Remove status and unavailable_until from data since they're already updated
            unset($data['status'], $data['unavailable_until']);
            
            // Update other fields
            if (!empty($data)) {
                $venue->update($data);
            }
            
            $message = 'Venue updated successfully.';
            if ($result['postponed_count'] > 0) {
                $message .= " {$result['postponed_count']} upcoming reservation(s) have been postponed.";
            }
            
            return redirect()->route('admin.venues.index')
                ->with('success', $message);
        } else {
            // No status change, just update normally
            $venue->update($data);
            
            return redirect()->route('admin.venues.index')
                ->with('success', 'Venue updated successfully.');
        }
    }

    /**
     * Remove the specified venue
     */
    public function destroy(Venue $venue)
    {
        $this->ensureAdmin();
        
        // Delete photo if exists
        if ($venue->photo && Storage::disk('public')->exists($venue->photo)) {
            Storage::disk('public')->delete($venue->photo);
        }
        
        $venue->delete();

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue deleted successfully.');
    }

    /**
     * Check if user is administrator or SSC Officer (for map view)
     */
    protected function ensureAdminOrSscOfficer()
    {
        $user = auth()->user();
        if (!$user->isAdministrator() && !$user->isSscOfficer()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Display venue map view
     */
    public function map(Request $request)
    {
        $this->ensureAdminOrSscOfficer();
        $venues = Venue::where('location', 'Santiago Campus')->get();
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        // Get venue availability for selected date
        $venueAvailability = [];
        $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);
        
        foreach ($venues as $venue) {
            // Get approved and pending reservations for the selected date (matching API logic)
            $reservations = $venue->reservations()
                ->where('date', $selectedDate)
                ->whereIn('status', ['approved', 'pending'])
                ->get();

            // Check if venue is currently occupied (has active reservation at current time)
            $isCurrentlyOccupied = false;
            $now = now();
            
            if ($selectedDateCarbon->isToday()) {
                foreach ($reservations as $reservation) {
                    $startDateTime = \Carbon\Carbon::parse($reservation->date->format('Y-m-d') . ' ' . $reservation->start_time);
                    $endDateTime = \Carbon\Carbon::parse($reservation->date->format('Y-m-d') . ' ' . $reservation->end_time);
                    
                    if ($now->between($startDateTime, $endDateTime)) {
                        $isCurrentlyOccupied = true;
                        break;
                    }
                }
            }

            // A venue is only available if:
            // 1. Its status is 'available' (not damaged or under_construction)
            // 2. It has no approved/pending reservations for the selected date
            $isAvailable = $venue->isAvailable() && $reservations->isEmpty();

            $venueAvailability[$venue->id] = [
                'is_available' => $isAvailable,
                'is_currently_occupied' => $isCurrentlyOccupied,
                'reservations' => $reservations,
                'reservation_count' => $reservations->count(),
            ];
        }

        $selectedVenueId = $request->get('venue_id', null);
        
        return view('admin.venues.map', compact('venues', 'selectedDate', 'venueAvailability', 'selectedVenueId'));
    }
}
