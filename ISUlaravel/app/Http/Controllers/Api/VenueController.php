<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Only return Santiago Campus venues for mobile app users
        $venues = Venue::where('location', 'Santiago Campus')
            ->latest()
            ->get();

        return response()->json([
            'data' => VenueResource::collection($venues),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue): JsonResponse
    {
        return response()->json([
            'data' => new VenueResource($venue),
        ]);
    }
}
