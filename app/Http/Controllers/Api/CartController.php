<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Service;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\PromotionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService,
        protected PromotionServiceInterface $promotionService,
        protected CouponServiceInterface $couponService
    ) {}

    /**
     * Get cart summary with pricing.
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
            // Optional: services the customer wants to add
            'selected_services' => 'nullable|array',
            'selected_services.*.service_id' => 'required|exists:services,id',
            'selected_services.*.scheduled_date' => 'nullable|date|after_or_equal:today',
            'selected_services.*.scheduled_time' => 'nullable|string',
        ]);

        $cartItems = [];
        $errors = [];

        foreach ($validated['items'] as $item) {
            $product = Product::with('images')->find($item['product_id']);
            $variation = isset($item['variation_id'])
                ? ProductVariation::find($item['variation_id'])
                : null;

            // Check availability — record error but still include item in pricing
            $available = $this->inventoryService->checkAvailability(
                $product,
                $item['quantity'],
                $variation
            );

            if (!$available) {
                $errors[] = "Insufficient stock for {$product->name}";
            }

            $price = $variation?->price ?? $product->price;

            $cartItems[] = [
                'product_id' => $product->id,
                'variation_id' => $variation?->id,
                'name' => $product->name,
                'sku' => $variation?->sku ?? $product->sku,
                'image' => $product->images->first()?->path,
                'price' => $price,
                'quantity' => $item['quantity'],
                'total' => $price * $item['quantity'],
                'in_stock' => $available,
            ];
        }

        // Apply promotions
        $promotionResult = $this->promotionService->applyPromotion($cartItems);

        // Raw subtotal before any discounts
        $rawSubtotal = collect($promotionResult['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        // Subtotal after all promotion discounts (product-level + cart-level)
        $promotionDiscount = $promotionResult['total_discount'];
        $subtotalAfterPromotion = $rawSubtotal - $promotionDiscount;

        // Apply coupon if provided (coupon is applied on top of promotion-discounted subtotal)
        $couponDiscount = 0;
        $couponError = null;
        if (!empty($validated['coupon_code'])) {
            $couponResult = $this->couponService->validateCoupon(
                $validated['coupon_code'],
                auth()->user()?->email ?? '',
                $cartItems,
                $subtotalAfterPromotion
            );

            if ($couponResult['valid']) {
                $couponDiscount = $couponResult['discount'];
            } else {
                $couponError = $couponResult['message'];
            }
        }

        // Calculate service costs and validate required services
        $serviceDetails  = [];
        $serviceErrors   = [];
        $totalServiceCost = 0;
        $missingRequired  = [];

        // Check which products have required services
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $requiredServiceIds = $product->requiredServices()->pluck('services.id')->toArray();
            $selectedIds = collect($validated['selected_services'] ?? [])->pluck('service_id')->toArray();

            foreach ($requiredServiceIds as $reqId) {
                if (!in_array($reqId, $selectedIds)) {
                    $svc = Service::find($reqId);
                    $missingRequired[] = "'{$product->name}' requires service: {$svc->name}";
                }
            }
        }

        foreach ($validated['selected_services'] ?? [] as $sel) {
            $service = Service::active()->find($sel['service_id']);
            if (!$service) {
                $serviceErrors[] = "Service ID {$sel['service_id']} is not available.";
                continue;
            }

            if ($service->requires_scheduling && empty($sel['scheduled_date'])) {
                $serviceErrors[] = "{$service->name} requires a scheduled date.";
            }

            // Per-product pivot price (use max across cart items that have this service)
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
                'service_id'     => $service->id,
                'name'           => $service->name,
                'type'           => $service->type,
                'type_display'   => $service->type_display,
                'price'          => $price,
                'scheduled_date' => $sel['scheduled_date'] ?? null,
                'scheduled_time' => $sel['scheduled_time'] ?? null,
            ];
        }

        $allErrors = array_merge($errors, $serviceErrors, $missingRequired);
        $grandTotal = $subtotalAfterPromotion - $couponDiscount + $totalServiceCost;

        return response()->json([
            'success' => true,
            'data' => [
                'items'              => $promotionResult['items'],
                'subtotal'           => $rawSubtotal,
                'promotion_discount' => $promotionDiscount,
                'coupon_discount'    => $couponDiscount,
                'coupon_error'       => $couponError,
                'service_amount'     => $totalServiceCost,
                'services'           => $serviceDetails,
                'total'              => $grandTotal,
                'errors'             => $allErrors,
                'missing_required_services' => $missingRequired,
            ],
        ]);
    }

    /**
     * Validate coupon.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $result = $this->couponService->validateCoupon(
            $validated['code'],
            auth()->user()?->email ?? '',
            $validated['items'],
            $validated['subtotal']
        );

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'] ?? null,
            'data' => $result['valid'] ? [
                'discount' => $result['discount'],
                'coupon' => $result['coupon'],
            ] : null,
        ]);
    }

    /**
     * Check product availability.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($validated['product_id']);
        $variation = isset($validated['variation_id']) 
            ? ProductVariation::find($validated['variation_id']) 
            : null;

        $available = $this->inventoryService->checkAvailability(
            $product,
            $validated['quantity'],
            $variation
        );

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $available,
            ],
        ]);
    }

    /**
     * Get all available valid coupons for public view.
     */
    public function getAvailableCoupons(): JsonResponse
    {
        $coupons = \App\Models\Coupon::valid()
            ->select([
                'id',
                'code',
                'name',
                'description',
                'discount_type',
                'discount_value',
                'min_order_amount',
                'max_discount_amount',
                'applies_to',
                'expires_at',
            ])
            ->orderBy('discount_value', 'desc')
            ->get()
            ->map(function ($coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                    'applies_to' => $coupon->applies_to,
                    'expires_at' => $coupon->expires_at?->format('Y-m-d H:i:s'),
                    'is_free_shipping' => $coupon->discount_type === 'free_shipping',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }
}
