<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerSegmentationService;
use Illuminate\Http\Request;

class CustomerAnalyticsController extends Controller
{
    protected $segmentationService;

    public function __construct(CustomerSegmentationService $segmentationService)
    {
        $this->segmentationService = $segmentationService;
    }

    /**
     * Get customer spending summary.
     */
    public function spendingSummary($customerId)
    {
        $customer = User::customers()->findOrFail($customerId);
        $summary = $this->segmentationService->getCustomerSpendingSummary($customer);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Calculate analytics for a specific customer.
     */
    public function calculateCustomer($customerId)
    {
        $customer = User::customers()->findOrFail($customerId);
        $analytics = $this->segmentationService->calculateCustomerAnalytics($customer);
        $this->segmentationService->autoAssignSegments($customer);

        return response()->json([
            'success' => true,
            'message' => 'Customer analytics calculated successfully',
            'data' => $analytics,
        ]);
    }

    /**
     * Get customer analytics dashboard.
     */
    public function dashboard()
    {
        $totalCustomers = User::customers()->count();
        $vipCustomers = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('is_vip', true))
            ->count();
        $highRiskCustomers = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('risk_level', 'high'))
            ->count();
        $repeatCustomers = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('completed_orders', '>=', 2))
            ->count();
        $newCustomers = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('completed_orders', '<=', 1))
            ->count();

        // Top spenders
        $topSpenders = User::customers()
            ->whereHas('analytics')
            ->with('analytics')
            ->get()
            ->sortByDesc('analytics.total_spent')
            ->take(10)
            ->values();

        // Recent VIP customers
        $recentVips = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('is_vip', true))
            ->with('analytics')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'vip_customers' => $vipCustomers,
                    'high_risk_customers' => $highRiskCustomers,
                    'repeat_customers' => $repeatCustomers,
                    'new_customers' => $newCustomers,
                ],
                'top_spenders' => $topSpenders,
                'recent_vips' => $recentVips,
            ],
        ]);
    }

    /**
     * Get customer growth report.
     */
    public function growthReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(6)->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $customers = User::customers()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Calculate retention rate
        $totalCustomers = User::customers()->count();
        $repeatCustomers = User::customers()
            ->whereHas('analytics', fn($q) => $q->where('completed_orders', '>=', 2))
            ->count();
        $retentionRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_growth' => $customers,
                'retention_rate' => round($retentionRate, 2),
                'total_customers' => $totalCustomers,
                'repeat_customers' => $repeatCustomers,
            ],
        ]);
    }

    /**
     * Get customer lifetime value distribution.
     */
    public function ltvDistribution()
    {
        $distribution = [
            '0-5000' => User::customers()
                ->whereHas('analytics', fn($q) => $q->whereBetween('lifetime_value', [0, 5000]))
                ->count(),
            '5001-10000' => User::customers()
                ->whereHas('analytics', fn($q) => $q->whereBetween('lifetime_value', [5001, 10000]))
                ->count(),
            '10001-20000' => User::customers()
                ->whereHas('analytics', fn($q) => $q->whereBetween('lifetime_value', [10001, 20000]))
                ->count(),
            '20001-50000' => User::customers()
                ->whereHas('analytics', fn($q) => $q->whereBetween('lifetime_value', [20001, 50000]))
                ->count(),
            '50001+' => User::customers()
                ->whereHas('analytics', fn($q) => $q->where('lifetime_value', '>', 50000))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $distribution,
        ]);
    }
}
