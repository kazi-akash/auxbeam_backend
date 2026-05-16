<?php

namespace App\Services;

use App\Models\User;
use App\Models\CustomerAnalytics;
use App\Models\CustomerSegment;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerSegmentationService
{
    /**
     * Calculate and update customer analytics.
     */
    public function calculateCustomerAnalytics(User $customer): CustomerAnalytics
    {
        $orders = $customer->orders();

        // Order statistics
        $totalOrders = $orders->count();
        $completedOrders = $orders->whereIn('status', ['delivered', 'complete'])->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();
        $returnedOrders = $customer->returns()->count();

        // Financial statistics
        $completedOrdersQuery = $orders->whereIn('status', ['delivered', 'complete']);
        $totalSpent = $completedOrdersQuery->sum('total_amount');
        $averageOrderValue = $completedOrders > 0 ? $totalSpent / $completedOrders : 0;
        $lifetimeValue = $totalSpent;

        // COD statistics
        $codOrders = $orders->where('payment_status', 'cod')->count();
        $codCompleted = $orders->where('payment_status', 'cod')
            ->whereIn('status', ['delivered', 'complete'])->count();
        $codCancelled = $orders->where('payment_status', 'cod')
            ->where('status', 'cancelled')->count();
        $codSuccessRate = $codOrders > 0 ? ($codCompleted / $codOrders) * 100 : 0;

        // Online payment statistics
        $onlinePaymentOrders = $orders->where('payment_status', 'paid')->count();
        $onlinePaymentCompleted = $orders->where('payment_status', 'paid')
            ->whereIn('status', ['delivered', 'complete'])->count();

        // Engagement metrics
        $firstOrder = $orders->oldest()->first();
        $lastOrder = $orders->latest()->first();
        $firstOrderAt = $firstOrder ? $firstOrder->created_at : null;
        $lastOrderAt = $lastOrder ? $lastOrder->created_at : null;
        $daysSinceLastOrder = $lastOrderAt ? Carbon::now()->diffInDays($lastOrderAt) : null;

        // Calculate order frequency (average days between orders)
        $orderFrequencyDays = null;
        if ($completedOrders > 1 && $firstOrderAt && $lastOrderAt) {
            $daysBetween = $firstOrderAt->diffInDays($lastOrderAt);
            $orderFrequencyDays = $daysBetween / ($completedOrders - 1);
        }

        // Calculate risk score (0-100, higher = more risky)
        $riskScore = $this->calculateRiskScore([
            'cod_success_rate' => $codSuccessRate,
            'cancelled_orders' => $cancelledOrders,
            'total_orders' => $totalOrders,
            'returned_orders' => $returnedOrders,
        ]);

        $riskLevel = $this->getRiskLevel($riskScore);

        // Calculate VIP score (0-100, higher = more valuable)
        $vipScore = $this->calculateVipScore([
            'total_spent' => $totalSpent,
            'completed_orders' => $completedOrders,
            'average_order_value' => $averageOrderValue,
            'order_frequency_days' => $orderFrequencyDays,
        ]);

        $isVip = $vipScore >= 70;

        // Update or create analytics record
        $analytics = CustomerAnalytics::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'returned_orders' => $returnedOrders,
                'total_spent' => $totalSpent,
                'average_order_value' => $averageOrderValue,
                'lifetime_value' => $lifetimeValue,
                'cod_orders' => $codOrders,
                'cod_completed' => $codCompleted,
                'cod_cancelled' => $codCancelled,
                'cod_success_rate' => $codSuccessRate,
                'online_payment_orders' => $onlinePaymentOrders,
                'online_payment_completed' => $onlinePaymentCompleted,
                'first_order_at' => $firstOrderAt,
                'last_order_at' => $lastOrderAt,
                'days_since_last_order' => $daysSinceLastOrder,
                'order_frequency_days' => $orderFrequencyDays,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'vip_score' => $vipScore,
                'is_vip' => $isVip,
                'last_calculated_at' => now(),
            ]
        );

        return $analytics;
    }

    /**
     * Calculate risk score based on customer behavior.
     */
    protected function calculateRiskScore(array $data): float
    {
        $score = 0;

        // COD success rate (40% weight)
        if ($data['cod_success_rate'] < 50) {
            $score += 40;
        } elseif ($data['cod_success_rate'] < 70) {
            $score += 25;
        } elseif ($data['cod_success_rate'] < 85) {
            $score += 10;
        }

        // Cancellation rate (30% weight)
        if ($data['total_orders'] > 0) {
            $cancellationRate = ($data['cancelled_orders'] / $data['total_orders']) * 100;
            if ($cancellationRate > 50) {
                $score += 30;
            } elseif ($cancellationRate > 30) {
                $score += 20;
            } elseif ($cancellationRate > 15) {
                $score += 10;
            }
        }

        // Return rate (30% weight)
        if ($data['total_orders'] > 0) {
            $returnRate = ($data['returned_orders'] / $data['total_orders']) * 100;
            if ($returnRate > 30) {
                $score += 30;
            } elseif ($returnRate > 15) {
                $score += 20;
            } elseif ($returnRate > 5) {
                $score += 10;
            }
        }

        return min($score, 100);
    }

    /**
     * Get risk level from score.
     */
    protected function getRiskLevel(float $score): string
    {
        if ($score >= 60) {
            return 'high';
        } elseif ($score >= 30) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Calculate VIP score based on customer value.
     */
    protected function calculateVipScore(array $data): float
    {
        $score = 0;

        // Total spent (40% weight)
        if ($data['total_spent'] >= 100000) {
            $score += 40;
        } elseif ($data['total_spent'] >= 50000) {
            $score += 30;
        } elseif ($data['total_spent'] >= 20000) {
            $score += 20;
        } elseif ($data['total_spent'] >= 10000) {
            $score += 10;
        }

        // Completed orders (30% weight)
        if ($data['completed_orders'] >= 20) {
            $score += 30;
        } elseif ($data['completed_orders'] >= 10) {
            $score += 20;
        } elseif ($data['completed_orders'] >= 5) {
            $score += 15;
        } elseif ($data['completed_orders'] >= 2) {
            $score += 10;
        }

        // Average order value (20% weight)
        if ($data['average_order_value'] >= 10000) {
            $score += 20;
        } elseif ($data['average_order_value'] >= 5000) {
            $score += 15;
        } elseif ($data['average_order_value'] >= 3000) {
            $score += 10;
        }

        // Order frequency (10% weight) - lower is better
        if ($data['order_frequency_days'] !== null) {
            if ($data['order_frequency_days'] <= 15) {
                $score += 10;
            } elseif ($data['order_frequency_days'] <= 30) {
                $score += 7;
            } elseif ($data['order_frequency_days'] <= 60) {
                $score += 5;
            }
        }

        return min($score, 100);
    }

    /**
     * Auto-assign segments to customer based on analytics.
     */
    public function autoAssignSegments(User $customer): void
    {
        $analytics = $customer->analytics;
        if (!$analytics) {
            $analytics = $this->calculateCustomerAnalytics($customer);
        }

        $segments = CustomerSegment::active()->get();
        $segmentsToAssign = [];

        foreach ($segments as $segment) {
            if ($this->matchesCriteria($analytics, $segment->criteria)) {
                $segmentsToAssign[] = $segment->id;
            }
        }

        // Sync segments (remove old, add new)
        $customer->segments()->sync($segmentsToAssign);
    }

    /**
     * Check if customer analytics matches segment criteria.
     */
    protected function matchesCriteria(CustomerAnalytics $analytics, ?array $criteria): bool
    {
        if (!$criteria) {
            return false;
        }

        foreach ($criteria as $key => $value) {
            $analyticsValue = $analytics->{str_replace(['min_', 'max_'], '', $key)};

            if (str_starts_with($key, 'min_')) {
                if ($analyticsValue < $value) {
                    return false;
                }
            } elseif (str_starts_with($key, 'max_')) {
                if ($analyticsValue > $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recalculate analytics for all customers.
     */
    public function recalculateAllCustomers(): int
    {
        $customers = User::customers()->get();
        $count = 0;

        foreach ($customers as $customer) {
            $this->calculateCustomerAnalytics($customer);
            $this->autoAssignSegments($customer);
            $count++;
        }

        return $count;
    }

    /**
     * Get customer spending summary.
     */
    public function getCustomerSpendingSummary(User $customer): array
    {
        $analytics = $customer->analytics;
        if (!$analytics) {
            $analytics = $this->calculateCustomerAnalytics($customer);
        }

        return [
            'customer_id' => $customer->id,
            'customer_name' => $customer->full_name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'total_orders' => $analytics->total_orders,
            'completed_orders' => $analytics->completed_orders,
            'cancelled_orders' => $analytics->cancelled_orders,
            'total_spent' => $analytics->total_spent,
            'average_order_value' => $analytics->average_order_value,
            'lifetime_value' => $analytics->lifetime_value,
            'cod_success_rate' => $analytics->cod_success_rate,
            'risk_level' => $analytics->risk_level,
            'risk_score' => $analytics->risk_score,
            'vip_score' => $analytics->vip_score,
            'is_vip' => $analytics->is_vip,
            'first_order_at' => $analytics->first_order_at,
            'last_order_at' => $analytics->last_order_at,
            'days_since_last_order' => $analytics->days_since_last_order,
            'segments' => $customer->segments->pluck('name'),
            'tags' => $customer->tags->pluck('name'),
        ];
    }
}
