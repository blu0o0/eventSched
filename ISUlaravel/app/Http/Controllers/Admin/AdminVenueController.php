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

        // Handle photo URL - download and store locally
        if ($request->filled('photo') && filter_var($request->photo, FILTER_VALIDATE_URL)) {
            try {
                $photoPath = $this->downloadAndStoreImage($request->photo);
                $data['photo'] = $photoPath;
            } catch (\Exception $e) {
                $data['photo'] = null;
            }
        } else {
            $data['photo'] = null;
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

        // Handle photo URL - download and store locally
        if ($request->filled('photo') && filter_var($request->photo, FILTER_VALIDATE_URL)) {
            // Delete old photo if it's a local file
            if ($venue->photo && Storage::disk('public')->exists($venue->photo)) {
                Storage::disk('public')->delete($venue->photo);
            }
            try {
                $photoPath = $this->downloadAndStoreImage($request->photo);
                $data['photo'] = $photoPath;
            } catch (\Exception $e) {
                // Keep existing photo if download fails
                unset($data['photo']);
            }
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
     * Download image from URL and store locally
     */
    private function downloadAndStoreImage($url)
    {
        // Ensure venues directory exists
        if (!Storage::disk('public')->exists('venues')) {
            Storage::disk('public')->makeDirectory('venues');
        }

        \Log::info('Attempting to download image from: ' . $url);

        // Try to download with curl if file_get_contents is disabled
        $contents = @file_get_contents($url);
        if ($contents === false) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $contents = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            \Log::info('CURL HTTP Code: ' . $httpCode);
            
            if ($contents === false || empty($contents)) {
                throw new \Exception('Failed to download image from URL');
            }
        }

        $filename = 'venues/' . uniqid() . '.jpg';
        Storage::disk('public')->put($filename, $contents);
        
        \Log::info('Image stored at: ' . $filename);
        \Log::info('Full path: ' . Storage::disk('public')->path($filename));
        
        return $filename;
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
        $venues = Venue::with('areas')->get();
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        // Get venue availability for selected date
        $venueAvailability = [];
        $selectedDateCarbon = \Carbon\Carbon::parse($selectedDate);
        
        // Get total reservations per venue (all time, all statuses)
        $totalReservations = \App\Models\Reservation::select('venue_id')
            ->selectRaw('count(*) as total_count')
            ->groupBy('venue_id')
            ->pluck('total_count', 'venue_id');
        
        // Get all reservations per venue (all time, all statuses) with relationships
        $allReservationsByVenue = \App\Models\Reservation::with(['user', 'area'])
            ->get()
            ->groupBy('venue_id')
            ->map(function ($reservations) {
                return $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'title' => $reservation->title,
                        'status' => $reservation->status,
                        'date' => $reservation->date,
                        'start_time' => $reservation->start_time,
                        'end_time' => $reservation->end_time,
                        'user' => $reservation->user ? [
                            'name' => $reservation->user->name,
                        ] : null,
                        'area' => $reservation->area ? [
                            'id' => $reservation->area->id,
                            'name' => $reservation->area->name,
                            'photo_url' => $reservation->area->photo_url,
                        ] : null,
                    ];
                })->values()->toArray();
            });
        
        foreach ($venues as $venue) {
            $allVenueReservations = $allReservationsByVenue->get($venue->id, []);
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
            
            $totalCount = $totalReservations->get($venue->id, 0);

            $venueAvailability[$venue->id] = [
                'is_available' => $isAvailable,
                'is_currently_occupied' => $isCurrentlyOccupied,
                'reservations' => $reservations,
                'reservation_count' => $reservations->count(),
                'total_reservations' => $totalCount,
                'all_reservations' => $allVenueReservations,
            ];
        }

        $selectedVenueId = $request->get('venue_id', null);
        
        // Get all areas with coordinates
        $areas = \App\Models\Area::whereNotNull('map_coordinates')
            ->where('map_coordinates', '!=', '')
            ->with('venue')
            ->get();
        
        // Get all reservations for the selected date
        $reservations = \App\Models\Reservation::where('date', $selectedDate)
            ->whereIn('status', ['approved', 'pending'])
            ->with(['user', 'venue', 'area'])
            ->get();
        
        return view('admin.venues.map', compact('venues', 'selectedDate', 'venueAvailability', 'selectedVenueId', 'areas', 'reservations'));
    }
}
