<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    /**
     * Get incomplete orders.
     */
    public function incomplete(Request $request)
    {
        $query = Order::incomplete()
            ->with(['user:id,first_name,last_name,email', 'items.product:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
            'summary' => [
                'total_incomplete' => Order::incomplete()->count(),
                'total_value' => Order::incomplete()->sum('total_amount'),
            ],
        ]);
    }

    /**
     * Get hourly orders report.
     */
    public function hourlyReport(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $hourlyData = Order::whereDate('created_at', $date)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_order_value')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Fill in missing hours with zero values
        $completeData = collect(range(0, 23))->map(function ($hour) use ($hourlyData) {
            $data = $hourlyData->firstWhere('hour', $hour);
            
            return [
                'hour' => $hour,
                'hour_label' => sprintf('%02d:00', $hour),
                'order_count' => $data ? $data->order_count : 0,
                'total_sales' => $data ? (float) $data->total_sales : 0,
                'average_order_value' => $data ? (float) $data->average_order_value : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $completeData,
            'summary' => [
                'total_orders' => $hourlyData->sum('order_count'),
                'total_sales' => $hourlyData->sum('total_sales'),
                'peak_hour' => $hourlyData->sortByDesc('order_count')->first()?->hour,
            ],
        ]);
    }

    /**
     * Get orders by source report.
     */
    public function bySource(Request $request)
    {
        $query = Order::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sourceData = $query->select(
                'order_source',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_order_value')
            )
            ->groupBy('order_source')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sourceData,
            'summary' => [
                'total_orders' => $sourceData->sum('order_count'),
                'total_sales' => $sourceData->sum('total_sales'),
            ],
        ]);
    }

    /**
     * Get orders by UTM campaign.
     */
    public function byUtmCampaign(Request $request)
    {
        $query = Order::whereNotNull('utm_campaign');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $campaignData = $query->select(
                'utm_campaign',
                'utm_source',
                'utm_medium',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_order_value')
            )
            ->groupBy('utm_campaign', 'utm_source', 'utm_medium')
            ->orderByDesc('order_count')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaignData,
            'summary' => [
                'total_campaigns' => $campaignData->count(),
                'total_orders' => $campaignData->sum('order_count'),
                'total_sales' => $campaignData->sum('total_sales'),
            ],
        ]);
    }

    /**
     * Get order status history.
     */
    public function statusHistory($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $history = $order->statusHistory()
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get orders needing follow-up.
     */
    public function needingFollowUp(Request $request)
    {
        $query = Order::needingFollowUp()
            ->with(['user:id,first_name,last_name,email', 'reminders' => function($q) {
                $q->where('is_completed', false)->orderBy('remind_at', 'asc');
            }])
            ->orderBy('follow_up_at', 'asc');

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
            'summary' => [
                'total_needing_follow_up' => Order::needingFollowUp()->count(),
            ],
        ]);
    }

    /**
     * Mark follow-up as completed.
     */
    public function completeFollowUp($orderId)
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'follow_up_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up marked as completed',
            'data' => $order,
        ]);
    }

    /**
     * Set follow-up reminder for order.
     */
    public function setFollowUp(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'follow_up_at' => 'required|date|after:now',
        ]);

        $order->update([
            'follow_up_at' => $validated['follow_up_at'],
            'follow_up_completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up reminder set successfully',
            'data' => $order,
        ]);
    }

    /**
     * Get order statistics by status.
     */
    public function statusStatistics(Request $request)
    {
        $query = Order::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $statusData = $query->select(
                'status',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $statusData,
            'summary' => [
                'total_orders' => $statusData->sum('order_count'),
                'total_sales' => $statusData->sum('total_sales'),
            ],
        ]);
    }
}
