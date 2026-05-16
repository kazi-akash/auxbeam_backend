<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnhancedReportController extends Controller
{
    /**
     * Profit report (Revenue - Cost).
     */
    public function profitReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['delivered', 'complete'])
            ->with('items')
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $cost = $item->cost_price ?? 0;
                $totalCost += $cost * $item->quantity;
            }
        }

        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Daily breakdown
        $dailyProfit = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function ($dayOrders) {
            $revenue = $dayOrders->sum('total_amount');
            $cost = 0;
            foreach ($dayOrders as $order) {
                foreach ($order->items as $item) {
                    $cost += ($item->cost_price ?? 0) * $item->quantity;
                }
            }
            return [
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'total_cost' => round($totalCost, 2),
                    'total_profit' => round($totalProfit, 2),
                    'profit_margin' => round($profitMargin, 2),
                    'total_orders' => $orders->count(),
                ],
                'daily_breakdown' => $dailyProfit,
            ],
        ]);
    }

    /**
     * COD vs Paid comparison report.
     */
    public function codVsPaidReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $codOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'cod')
            ->get();

        $paidOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->get();

        $codStats = [
            'total_orders' => $codOrders->count(),
            'completed' => $codOrders->whereIn('status', ['delivered', 'complete'])->count(),
            'cancelled' => $codOrders->where('status', 'cancelled')->count(),
            'pending' => $codOrders->where('status', 'pending')->count(),
            'total_amount' => $codOrders->sum('total_amount'),
            'completed_amount' => $codOrders->whereIn('status', ['delivered', 'complete'])->sum('total_amount'),
            'success_rate' => 0,
        ];

        if ($codStats['total_orders'] > 0) {
            $codStats['success_rate'] = round(($codStats['completed'] / $codStats['total_orders']) * 100, 2);
        }

        $paidStats = [
            'total_orders' => $paidOrders->count(),
            'completed' => $paidOrders->whereIn('status', ['delivered', 'complete'])->count(),
            'cancelled' => $paidOrders->where('status', 'cancelled')->count(),
            'pending' => $paidOrders->where('status', 'pending')->count(),
            'total_amount' => $paidOrders->sum('total_amount'),
            'completed_amount' => $paidOrders->whereIn('status', ['delivered', 'complete'])->sum('total_amount'),
            'success_rate' => 0,
        ];

        if ($paidStats['total_orders'] > 0) {
            $paidStats['success_rate'] = round(($paidStats['completed'] / $paidStats['total_orders']) * 100, 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cod' => $codStats,
                'paid' => $paidStats,
                'comparison' => [
                    'cod_percentage' => $codStats['total_orders'] + $paidStats['total_orders'] > 0 
                        ? round(($codStats['total_orders'] / ($codStats['total_orders'] + $paidStats['total_orders'])) * 100, 2)
                        : 0,
                    'paid_percentage' => $codStats['total_orders'] + $paidStats['total_orders'] > 0 
                        ? round(($paidStats['total_orders'] / ($codStats['total_orders'] + $paidStats['total_orders'])) * 100, 2)
                        : 0,
                ],
            ],
        ]);
    }

    /**
     * Customer growth report.
     */
    public function customerGrowthReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $monthlyGrowth = User::customers()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalCustomers = User::customers()->count();
        $newCustomersThisMonth = User::customers()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $activeCustomers = User::customers()
            ->whereHas('orders', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })
            ->count();

        $repeatCustomers = User::customers()
            ->whereHas('analytics', function ($q) {
                $q->where('completed_orders', '>=', 2);
            })
            ->count();

        $retentionRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'new_this_month' => $newCustomersThisMonth,
                    'active_customers' => $activeCustomers,
                    'repeat_customers' => $repeatCustomers,
                    'retention_rate' => $retentionRate,
                ],
                'monthly_growth' => $monthlyGrowth,
            ],
        ]);
    }

    /**
     * Product performance report.
     */
    public function productPerformanceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $limit = $request->get('limit', 20);

        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['delivered', 'complete']);
            })
            ->with('product')
            ->select('product_id', 
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    /**
     * Order source performance report.
     */
    public function orderSourceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $sourceStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('order_source',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_order_value'),
                DB::raw('SUM(CASE WHEN status IN ("delivered", "complete") THEN 1 ELSE 0 END) as completed_orders')
            )
            ->groupBy('order_source')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sourceStats,
        ]);
    }

    /**
     * UTM campaign performance report.
     */
    public function utmCampaignReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $campaignStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('utm_campaign')
            ->select('utm_campaign', 'utm_source', 'utm_medium',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_order_value'),
                DB::raw('SUM(CASE WHEN status IN ("delivered", "complete") THEN 1 ELSE 0 END) as completed_orders')
            )
            ->groupBy('utm_campaign', 'utm_source', 'utm_medium')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaignStats,
        ]);
    }
}
