<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAreaController extends Controller
{
    public function __construct()
    {
    }

    protected function ensureAdmin()
    {
        if (!auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $query = Area::with('venue');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by venue
        if ($request->has('venue_id') && $request->venue_id) {
            $query->where('venue_id', $request->venue_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $areas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $venues = Venue::orderBy('name')->get();
        $search = $request->get('search', '');
        $venueId = $request->get('venue_id', '');

        return view('admin.areas.index', compact('areas', 'venues', 'search', 'venueId'));
    }

    public function create()
    {
        $this->ensureAdmin();
        $venues = Venue::orderBy('name')->get();
        return view('admin.areas.create', compact('venues'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'status' => 'required|in:available,occupied,not_available',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('areas', 'public');
        }

        Area::create($validated);

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area created successfully.');
    }

    public function edit(Area $area)
    {
        $this->ensureAdmin();
        $venues = Venue::orderBy('name')->get();
        return view('admin.areas.edit', compact('area', 'venues'));
    }

    public function update(Request $request, Area $area)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'status' => 'required|in:available,occupied,not_available',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            if ($area->photo && Storage::disk('public')->exists($area->photo)) {
                Storage::disk('public')->delete($area->photo);
            }
            $validated['photo'] = $request->file('photo')->store('areas', 'public');
        } elseif ($request->has('remove_photo') && $request->remove_photo == '1') {
            if ($area->photo && Storage::disk('public')->exists($area->photo)) {
                Storage::disk('public')->delete($area->photo);
            }
            $validated['photo'] = null;
        } else {
            unset($validated['photo']);
        }

        $area->update($validated);

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        $this->ensureAdmin();

        if ($area->photo && Storage::disk('public')->exists($area->photo)) {
            Storage::disk('public')->delete($area->photo);
        }

        $area->delete();

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area deleted successfully.');
    }
}