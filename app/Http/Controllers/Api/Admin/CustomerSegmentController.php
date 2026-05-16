<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSegment;
use App\Models\User;
use App\Services\CustomerSegmentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerSegmentController extends Controller
{
    protected $segmentationService;

    public function __construct(CustomerSegmentationService $segmentationService)
    {
        $this->segmentationService = $segmentationService;
    }

    /**
     * Get all customer segments.
     */
    public function index(Request $request)
    {
        $query = CustomerSegment::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $segments = $query->ordered()
            ->withCount('customers')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $segments,
        ]);
    }

    /**
     * Create a new segment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:customer_segments,name',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'criteria' => 'nullable|array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $segment = CustomerSegment::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer segment created successfully',
            'data' => $segment,
        ], 201);
    }

    /**
     * Get segment details.
     */
    public function show($id)
    {
        $segment = CustomerSegment::with('customers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $segment,
        ]);
    }

    /**
     * Update segment.
     */
    public function update(Request $request, $id)
    {
        $segment = CustomerSegment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:customer_segments,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'criteria' => 'nullable|array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $segment->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer segment updated successfully',
            'data' => $segment,
        ]);
    }

    /**
     * Delete segment.
     */
    public function destroy($id)
    {
        $segment = CustomerSegment::findOrFail($id);
        $segment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer segment deleted successfully',
        ]);
    }

    /**
     * Get customers in a segment.
     */
    public function customers($id, Request $request)
    {
        $segment = CustomerSegment::findOrFail($id);

        $query = $segment->customers()->with('analytics');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Assign customers to segment.
     */
    public function assignCustomers(Request $request, $id)
    {
        $segment = CustomerSegment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assignData = [];
        foreach ($request->customer_ids as $customerId) {
            $assignData[$customerId] = [
                'assigned_at' => now(),
                'assigned_by' => 'admin',
                'notes' => $request->notes,
            ];
        }

        $segment->customers()->syncWithoutDetaching($assignData);

        return response()->json([
            'success' => true,
            'message' => 'Customers assigned to segment successfully',
        ]);
    }

    /**
     * Remove customers from segment.
     */
    public function removeCustomers(Request $request, $id)
    {
        $segment = CustomerSegment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $segment->customers()->detach($request->customer_ids);

        return response()->json([
            'success' => true,
            'message' => 'Customers removed from segment successfully',
        ]);
    }

    /**
     * Auto-assign segments to all customers.
     */
    public function autoAssignAll()
    {
        $count = $this->segmentationService->recalculateAllCustomers();

        return response()->json([
            'success' => true,
            'message' => "Analytics calculated and segments assigned for {$count} customers",
            'count' => $count,
        ]);
    }

    /**
     * Get VIP customers.
     */
    public function vipCustomers(Request $request)
    {
        $query = User::customers()
            ->whereHas('analytics', function ($q) {
                $q->where('is_vip', true);
            })
            ->with('analytics', 'segments', 'tags');

        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get COD risk customers.
     */
    public function codRiskCustomers(Request $request)
    {
        $query = User::customers()
            ->whereHas('analytics', function ($q) {
                $q->where('risk_level', 'high')
                    ->where('cod_orders', '>=', 2);
            })
            ->with('analytics', 'segments', 'tags');

        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get repeat customers.
     */
    public function repeatCustomers(Request $request)
    {
        $query = User::customers()
            ->whereHas('analytics', function ($q) {
                $q->where('completed_orders', '>=', 2);
            })
            ->with('analytics', 'segments', 'tags');

        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }
}
