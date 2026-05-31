<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaPixelConfiguration;
use App\Models\MetaPixelEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MetaPixelController extends Controller
{
    // ─── Configuration ────────────────────────────────────────────────────────

    /**
     * Get the current Meta Pixel configuration.
     */
    public function getConfiguration(): JsonResponse
    {
        $config = MetaPixelConfiguration::first();

        return response()->json([
            'success' => true,
            'data'    => $config,
        ]);
    }

    /**
     * Save (create or update) the Meta Pixel configuration.
     */
    public function saveConfiguration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pixel_id'               => 'required|string|max:50',
            'access_token'           => 'nullable|string',
            'is_active'              => 'boolean',
            'enable_pixel'           => 'boolean',
            'enable_conversion_api'  => 'boolean',
            'events_to_track'        => 'nullable|array',
            'events_to_track.*'      => 'string|in:ViewContent,AddToCart,InitiateCheckout,Purchase',
            'settings'               => 'nullable|array',
        ]);

        $config = MetaPixelConfiguration::first();

        if ($config) {
            // Only update access_token if explicitly provided (not empty string)
            if (array_key_exists('access_token', $validated) && $validated['access_token'] === '') {
                unset($validated['access_token']);
            }
            $config->update($validated);
        } else {
            $config = MetaPixelConfiguration::create($validated);
        }

        // Return config without exposing the access token value
        $response = $config->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Meta Pixel configuration saved.',
            'data'    => $response,
        ]);
    }

    /**
     * Toggle the pixel active state.
     */
    public function toggleActive(): JsonResponse
    {
        $config = MetaPixelConfiguration::first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No configuration found. Please save a configuration first.',
            ], 404);
        }

        $config->update(['is_active' => !$config->is_active]);

        return response()->json([
            'success' => true,
            'message' => $config->is_active ? 'Meta Pixel enabled.' : 'Meta Pixel disabled.',
            'data'    => $config,
        ]);
    }

    // ─── Event Logging ────────────────────────────────────────────────────────

    /**
     * Log a pixel event (called from frontend or server-side).
     */
    public function logEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name'  => 'required|string|in:ViewContent,AddToCart,InitiateCheckout,Purchase',
            'event_data'  => 'required|array',
            'source'      => 'nullable|string|in:browser,server',
            'order_id'    => 'nullable|integer|exists:orders,id',
        ]);

        $event = MetaPixelEvent::create([
            'user_id'    => auth()->id(),
            'order_id'   => $validated['order_id'] ?? null,
            'event_name' => $validated['event_name'],
            'event_id'   => Str::uuid()->toString(),
            'event_data' => $validated['event_data'],
            'source'     => $validated['source'] ?? 'browser',
        ]);

        return response()->json([
            'success' => true,
            'data'    => ['event_id' => $event->event_id],
        ], 201);
    }

    // ─── Events List ──────────────────────────────────────────────────────────

    /**
     * List pixel events with filters.
     */
    public function events(Request $request): JsonResponse
    {
        $perPage   = $request->integer('per_page', 20);
        $eventName = $request->input('event_name');
        $source    = $request->input('source');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = MetaPixelEvent::with(['user:id,first_name,last_name,email', 'order:id,order_number'])
            ->orderBy('created_at', 'desc');

        if ($eventName) {
            $query->eventName($eventName);
        }

        if ($source) {
            $query->source($source);
        }

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $events,
        ]);
    }

    // ─── Statistics ───────────────────────────────────────────────────────────

    /**
     * Get Meta Pixel event statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->startOfDay());
        $dateTo   = $request->input('date_to', now()->endOfDay());

        // Event counts by name
        $eventCounts = MetaPixelEvent::dateRange($dateFrom, $dateTo)
            ->select('event_name', DB::raw('COUNT(*) as count'))
            ->groupBy('event_name')
            ->pluck('count', 'event_name');

        // Source breakdown
        $sourceCounts = MetaPixelEvent::dateRange($dateFrom, $dateTo)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source');

        // Daily trend (last 30 days)
        $dailyTrend = MetaPixelEvent::dateRange($dateFrom, $dateTo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                'event_name',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'event_name')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(fn($rows) => $rows->pluck('count', 'event_name'));

        // Funnel conversion
        $viewContent      = $eventCounts['ViewContent']      ?? 0;
        $addToCart        = $eventCounts['AddToCart']        ?? 0;
        $initiateCheckout = $eventCounts['InitiateCheckout'] ?? 0;
        $purchase         = $eventCounts['Purchase']         ?? 0;

        $funnel = [
            'ViewContent'      => $viewContent,
            'AddToCart'        => $addToCart,
            'InitiateCheckout' => $initiateCheckout,
            'Purchase'         => $purchase,
            'view_to_cart_rate'        => $viewContent > 0 ? round(($addToCart / $viewContent) * 100, 2) : 0,
            'cart_to_checkout_rate'    => $addToCart > 0 ? round(($initiateCheckout / $addToCart) * 100, 2) : 0,
            'checkout_to_purchase_rate'=> $initiateCheckout > 0 ? round(($purchase / $initiateCheckout) * 100, 2) : 0,
            'overall_conversion_rate'  => $viewContent > 0 ? round(($purchase / $viewContent) * 100, 2) : 0,
        ];

        // UTM campaign performance (from orders)
        $utmPerformance = \App\Models\Order::whereNotNull('utm_source')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                'utm_source',
                'utm_medium',
                'utm_campaign',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'event_counts'    => $eventCounts,
                'source_counts'   => $sourceCounts,
                'daily_trend'     => $dailyTrend,
                'funnel'          => $funnel,
                'utm_performance' => $utmPerformance,
                'total_events'    => array_sum($eventCounts->toArray()),
                'date_from'       => $dateFrom,
                'date_to'         => $dateTo,
            ],
        ]);
    }

    // ─── Public Pixel Config (for frontend script injection) ──────────────────

    /**
     * Get public pixel config (pixel_id only, no secrets).
     * Used by the frontend to initialise the Facebook Pixel script.
     */
    public function publicConfig(): JsonResponse
    {
        $config = MetaPixelConfiguration::where('is_active', true)
            ->where('enable_pixel', true)
            ->select('pixel_id', 'enable_pixel', 'enable_conversion_api', 'events_to_track')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $config,
        ]);
    }
}
