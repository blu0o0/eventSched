<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the areas.
     */
    public function index(Request $request)
    {
        $query = Area::with('venue');
        
        // Filter by venue if provided
        if ($request->has('venue_id') && $request->venue_id) {
            $query->where('venue_id', $request->venue_id);
        }
        
        $areas = $query->orderBy('name')->get();
        
        return response()->json($areas);
    }

    /**
     * Display the specified area.
     */
    public function show(Area $area)
    {
        $area->load('venue');
        return response()->json($area);
    }
}