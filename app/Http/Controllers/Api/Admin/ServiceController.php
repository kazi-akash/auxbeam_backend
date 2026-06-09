<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * List all services.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $services = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $services,
            'types' => Service::TYPES,
        ]);
    }

    /**
     * Create a new service.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'slug'                 => 'nullable|string|unique:services,slug',
            'description'          => 'nullable|string',
            'type'                 => 'required|in:home_service,office_booking,home_delivery,outlet_pickup',
            'requires_scheduling'  => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'sort_order'           => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service created.',
            'data'    => $service,
        ], 201);
    }

    /**
     * Show a single service.
     */
    public function show(int $id): JsonResponse
    {
        $service = Service::with('products')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $service,
        ]);
    }

    /**
     * Update a service.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'slug'                => 'sometimes|string|unique:services,slug,' . $id,
            'description'         => 'nullable|string',
            'type'                => 'sometimes|in:home_service,office_booking,home_delivery,outlet_pickup',
            'requires_scheduling' => 'nullable|boolean',
            'is_active'           => 'nullable|boolean',
            'sort_order'          => 'nullable|integer|min:0',
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service updated.',
            'data'    => $service->fresh(),
        ]);
    }

    /**
     * Delete a service.
     */
    public function destroy(int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted.',
        ]);
    }

    /**
     * Toggle service active status.
     */
    public function toggle(int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);

        return response()->json([
            'success' => true,
            'data'    => ['is_active' => $service->is_active],
        ]);
    }

    // -------------------------------------------------------------------------
    // Product <-> Service attachment (admin product create/edit)
    // -------------------------------------------------------------------------

    /**
     * List services attached to a product.
     */
    public function productServices(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $services = $product->services()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'type'         => $s->type,
                'type_display' => $s->type_display,
                'price'        => $s->pivot->price,
                'is_required'  => (bool) $s->pivot->is_required,
                'is_active'    => (bool) $s->pivot->is_active,
                'conditions'   => $s->pivot->conditions,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $services,
        ]);
    }

    /**
     * Attach a service to a product (or update existing pivot).
     */
    public function attachService(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'service_id'  => 'required|exists:services,id',
            'price'       => 'required|numeric|min:0',
            'is_required' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'conditions'  => 'nullable|string',
        ]);

        $product->services()->syncWithoutDetaching([
            $validated['service_id'] => [
                'price'       => $validated['price'],
                'is_required' => $validated['is_required'] ?? false,
                'is_active'   => $validated['is_active'] ?? true,
                'conditions'  => $validated['conditions'] ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service attached to product.',
        ]);
    }

    /**
     * Sync all services for a product at once (bulk replace).
     *
     * Payload: { "services": [{ "service_id": 1, "price": 200, "is_required": true, ... }] }
     */
    public function syncServices(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'services'              => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price'      => 'required|numeric|min:0',
            'services.*.is_required'=> 'nullable|boolean',
            'services.*.is_active'  => 'nullable|boolean',
            'services.*.conditions' => 'nullable|string',
        ]);

        $syncData = [];
        foreach ($validated['services'] as $s) {
            $syncData[$s['service_id']] = [
                'price'       => $s['price'],
                'is_required' => $s['is_required'] ?? false,
                'is_active'   => $s['is_active'] ?? true,
                'conditions'  => $s['conditions'] ?? null,
            ];
        }

        $product->services()->sync($syncData);

        return response()->json([
            'success' => true,
            'message' => 'Product services synced.',
        ]);
    }

    /**
     * Detach a service from a product.
     */
    public function detachService(int $productId, int $serviceId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $product->services()->detach($serviceId);

        return response()->json([
            'success' => true,
            'message' => 'Service detached from product.',
        ]);
    }

    /**
     * Update a single pivot (price / required / active / conditions).
     */
    public function updateProductService(Request $request, int $productId, int $serviceId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'price'       => 'sometimes|numeric|min:0',
            'is_required' => 'sometimes|boolean',
            'is_active'   => 'sometimes|boolean',
            'conditions'  => 'nullable|string',
        ]);

        $product->services()->updateExistingPivot($serviceId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product service updated.',
        ]);
    }
}
