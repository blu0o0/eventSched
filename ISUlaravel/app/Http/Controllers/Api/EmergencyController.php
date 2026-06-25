<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmergencyReportRequest;
use App\Http\Resources\EmergencyReportResource;
use App\Models\EmergencyReport;
use App\Services\EmergencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function __construct(
        protected EmergencyService $emergencyService
    ) {
    }

    /**
     * Store a newly created emergency report.
     */
    public function store(StoreEmergencyReportRequest $request): JsonResponse
    {
        $this->authorize('create', EmergencyReport::class);

        try {
            $report = $this->emergencyService->create(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'message' => 'Emergency report submitted successfully',
                'data' => new EmergencyReportResource($report),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit emergency report',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
      * Display a listing of emergency reports (public access).
      */
     public function list(Request $request): JsonResponse
     {
         $reports = EmergencyReport::with('reporter')
             ->latest()
             ->paginate(15);
 
         return response()->json([
             'data' => EmergencyReportResource::collection($reports),
             'meta' => [
                 'current_page' => $reports->currentPage(),
                 'last_page' => $reports->lastPage(),
                 'per_page' => $reports->perPage(),
                 'total' => $reports->total(),
             ],
         ]);
     }

    /**
     * Display the specified emergency report.
     */
    public function show(EmergencyReport $emergency): JsonResponse
    {
        $this->authorize('view', $emergency);

        return response()->json([
            'data' => new EmergencyReportResource($emergency->load('reporter')),
        ]);
    }
}
