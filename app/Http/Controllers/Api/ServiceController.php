<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * List all active services (for checkout delivery type selection).
     */
    public function index(): JsonResponse
    {
        $services = Service::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($s) => [
                'id'                  => $s->id,
                'name'                => $s->name,
                'slug'                => $s->slug,
                'description'         => $s->description,
                'type'                => $s->type,
                'type_display'        => $s->type_display,
                'requires_scheduling' => $s->requires_scheduling,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $services,
        ]);
    }

    /**
     * Get active services for a specific product (for product page / cart).
     * Returns both optional and required services with per-product pricing.
     */
    public function productServices(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $services = $product->activeServices()
            ->where('services.is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($s) => [
                'id'                  => $s->id,
                'name'                => $s->name,
                'slug'                => $s->slug,
                'description'         => $s->description,
                'type'                => $s->type,
                'type_display'        => $s->type_display,
                'requires_scheduling' => $s->requires_scheduling,
                'price'               => (float) $s->pivot->price,
                'is_required'         => (bool) $s->pivot->is_required,
                'conditions'          => $s->pivot->conditions,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $services,
        ]);
    }

    /**
     * Validate selected services against a cart and return pricing.
     *
     * POST /api/services/validate
     * Body: {
     *   "items": [{ "product_id": 1, "quantity": 2 }],
     *   "selected_services": [{ "service_id": 1, "scheduled_date": "2026-06-10", "scheduled_time": "10:00" }]
     * }
     */
    public function validateServices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                            => 'required|array|min:1',
            'items.*.product_id'               => 'required|exists:products,id',
            'items.*.quantity'                 => 'required|integer|min:1',
            'selected_services'                => 'required|array|min:1',
            'selected_services.*.service_id'   => 'required|exists:services,id',
            'selected_services.*.scheduled_date'=> 'nullable|date|after_or_equal:today',
            'selected_services.*.scheduled_time'=> 'nullable|string',
        ]);

        $serviceId = collect($validated['selected_services'])->pluck('service_id')->unique();
        $services  = Service::active()->whereIn('id', $serviceId)->get()->keyBy('id');

        $errors          = [];
        $serviceDetails  = [];
        $totalServiceCost = 0;

        foreach ($validated['selected_services'] as $sel) {
            $service = $services->get($sel['service_id']);

            if (!$service) {
                $errors[] = "Service ID {$sel['service_id']} is not available.";
                continue;
            }

            if ($service->requires_scheduling && empty($sel['scheduled_date'])) {
                $errors[] = "{$service->name} requires a scheduled date.";
            }

            // Find the highest price across products for this service
            // (each product can have its own price for the service)
            $price = 0;
            foreach ($validated['items'] as $item) {
                $pivot = $service->products()
                    ->where('products.id', $item['product_id'])
                    ->wherePivot('is_active', true)
                    ->first();

                if ($pivot) {
                    $price = max($price, (float) $pivot->pivot->price);
                }
            }

            $totalServiceCost += $price;

            $serviceDetails[] = [
                'service_id'      => $service->id,
                'name'            => $service->name,
                'type'            => $service->type,
                'type_display'    => $service->type_display,
                'price'           => $price,
                'scheduled_date'  => $sel['scheduled_date'] ?? null,
                'scheduled_time'  => $sel['scheduled_time'] ?? null,
            ];
        }

        // Check for missing required services
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $requiredServices = $product->requiredServices()->pluck('services.id')->toArray();

            foreach ($requiredServices as $reqId) {
                $selected = collect($validated['selected_services'])->pluck('service_id')->contains($reqId);
                if (!$selected) {
                    $reqService = Service::find($reqId);
                    $errors[] = "{$product->name} requires the service: {$reqService->name}.";
                }
            }
        }

        return response()->json([
            'success'            => empty($errors),
            'data'               => [
                'services'           => $serviceDetails,
                'total_service_cost' => $totalServiceCost,
                'errors'             => $errors,
            ],
        ]);
    }

    /**
     * Get allowed payment methods for a given delivery type.
     *
     * GET /api/services/payment-methods?delivery_type=home_service
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $deliveryType = $request->query('delivery_type');

        $allowed = Order::PAYMENT_METHODS_BY_DELIVERY[$deliveryType] ?? array_keys(array_fill(0, 0, null));

        if (empty($allowed)) {
            $allowed = ['ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'pos_on_delivery'];
        }

        $labels = [
            'ssl_commerz'      => 'Credit / Debit Card (SSL Commerz)',
            'bkash'            => 'bKash',
            'nagad'            => 'Nagad',
            'cash_on_delivery' => 'Cash on Delivery',
            'pos_on_delivery'  => 'POS on Delivery',
            'cash_on_service'  => 'Cash on Service',
            'pos_on_service'   => 'POS on Service',
        ];

        $methods = collect($allowed)->map(fn($m) => [
            'value' => $m,
            'label' => $labels[$m] ?? $m,
        ])->values();

        return response()->json([
            'success' => true,
            'data'    => $methods,
        ]);
    }

    /**
     * Get order services (for checkout review / order detail).
     */
    public function orderServices(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('orderServices.service')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $order->orderServices,
        ]);
    }
}
