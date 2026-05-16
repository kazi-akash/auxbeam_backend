<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerTagController extends Controller
{
    /**
     * Get all customer tags.
     */
    public function index(Request $request)
    {
        $query = CustomerTag::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $tags = $query->orderBy('name')
            ->withCount('customers')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Create a new tag.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:customer_tags,name',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag = CustomerTag::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer tag created successfully',
            'data' => $tag,
        ], 201);
    }

    /**
     * Get tag details.
     */
    public function show($id)
    {
        $tag = CustomerTag::with('customers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    /**
     * Update tag.
     */
    public function update(Request $request, $id)
    {
        $tag = CustomerTag::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:customer_tags,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer tag updated successfully',
            'data' => $tag,
        ]);
    }

    /**
     * Delete tag.
     */
    public function destroy($id)
    {
        $tag = CustomerTag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer tag deleted successfully',
        ]);
    }

    /**
     * Assign tag to customers.
     */
    public function assignToCustomers(Request $request, $id)
    {
        $tag = CustomerTag::findOrFail($id);

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

        $assignData = [];
        foreach ($request->customer_ids as $customerId) {
            $assignData[$customerId] = [
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ];
        }

        $tag->customers()->syncWithoutDetaching($assignData);

        return response()->json([
            'success' => true,
            'message' => 'Tag assigned to customers successfully',
        ]);
    }

    /**
     * Remove tag from customers.
     */
    public function removeFromCustomers(Request $request, $id)
    {
        $tag = CustomerTag::findOrFail($id);

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

        $tag->customers()->detach($request->customer_ids);

        return response()->json([
            'success' => true,
            'message' => 'Tag removed from customers successfully',
        ]);
    }

    /**
     * Get customers with a specific tag.
     */
    public function customers($id, Request $request)
    {
        $tag = CustomerTag::findOrFail($id);

        $query = $tag->customers()->with('analytics', 'segments');

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
}
