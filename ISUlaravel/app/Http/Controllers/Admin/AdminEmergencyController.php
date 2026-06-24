<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyReport;
use App\Services\EmergencyService;
use Illuminate\Http\Request;

class AdminEmergencyController extends Controller
{
    public function __construct(
        protected EmergencyService $emergencyService
    ) {
    }

    /**
     * Show the form to create a new emergency report
     */
    public function create()
    {
        $this->ensureAdmin();
        return view('admin.emergency.create');
    }

    /**
     * Store a newly created emergency report
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $this->emergencyService->create($data, $request->user()->id);

        return redirect()
            ->route('admin.emergency.index')
            ->with('success', 'Emergency report created and marked as open.');
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
     * Display a listing of emergency reports
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();
        $query = EmergencyReport::with('reporter');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $emergencies = $query->latest()->paginate(15);

        return view('admin.emergency.index', compact('emergencies'));
    }

    /**
     * Display the specified emergency report
     */
    public function show(EmergencyReport $emergency)
    {
        $this->ensureAdmin();
        $emergency->load(['reporter', 'resolver']);

        return view('admin.emergency.show', compact('emergency'));
    }

    /**
     * Resolve an emergency report
     */
    public function resolve(Request $request, EmergencyReport $emergency)
    {
        $this->ensureAdmin();
        try {
            $this->emergencyService->resolve($emergency, $request->user()->id);

            return redirect()->route('admin.emergency.index')
                ->with('success', 'Emergency report resolved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete an emergency report
     */
    public function destroy(EmergencyReport $emergency)
    {
        $this->ensureAdmin();
        $emergency->delete();

        return redirect()->route('admin.emergency.index')
            ->with('success', 'Emergency report deleted successfully.');
    }
}
