<?php

namespace Database\Seeders;

use App\Models\CustomerSegment;
use App\Models\CustomerTag;
use Illuminate\Database\Seeder;

class CustomerSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default customer segments
        $segments = [
            [
                'name' => 'VIP Customer',
                'slug' => 'vip-customer',
                'description' => 'High-value customers with significant spending history',
                'color' => '#FFD700',
                'icon' => 'crown',
                'criteria' => [
                    'min_total_spent' => 50000,
                    'min_completed_orders' => 5,
                    'vip_score' => 70,
                ],
                'is_active' => true,
                'priority' => 100,
            ],
            [
                'name' => 'Repeat Customer',
                'slug' => 'repeat-customer',
                'description' => 'Customers who have made multiple purchases',
                'color' => '#10B981',
                'icon' => 'refresh',
                'criteria' => [
                    'min_completed_orders' => 2,
                ],
                'is_active' => true,
                'priority' => 80,
            ],
            [
                'name' => 'COD Risk',
                'slug' => 'cod-risk',
                'description' => 'Customers with high COD cancellation rate',
                'color' => '#EF4444',
                'icon' => 'alert-triangle',
                'criteria' => [
                    'min_cod_orders' => 2,
                    'max_cod_success_rate' => 50,
                ],
                'is_active' => true,
                'priority' => 90,
            ],
            [
                'name' => 'New Customer',
                'slug' => 'new-customer',
                'description' => 'First-time customers',
                'color' => '#3B82F6',
                'icon' => 'user-plus',
                'criteria' => [
                    'max_completed_orders' => 1,
                ],
                'is_active' => true,
                'priority' => 60,
            ],
            [
                'name' => 'Inactive Customer',
                'slug' => 'inactive-customer',
                'description' => 'Customers who haven\'t ordered in a long time',
                'color' => '#6B7280',
                'icon' => 'user-x',
                'criteria' => [
                    'min_days_since_last_order' => 90,
                    'min_completed_orders' => 1,
                ],
                'is_active' => true,
                'priority' => 50,
            ],
            [
                'name' => 'High Spender',
                'slug' => 'high-spender',
                'description' => 'Customers with high average order value',
                'color' => '#8B5CF6',
                'icon' => 'trending-up',
                'criteria' => [
                    'min_average_order_value' => 5000,
                ],
                'is_active' => true,
                'priority' => 85,
            ],
            [
                'name' => 'Frequent Buyer',
                'slug' => 'frequent-buyer',
                'description' => 'Customers who order frequently',
                'color' => '#F59E0B',
                'icon' => 'zap',
                'criteria' => [
                    'min_completed_orders' => 10,
                    'max_order_frequency_days' => 30,
                ],
                'is_active' => true,
                'priority' => 75,
            ],
        ];

        foreach ($segments as $segment) {
            CustomerSegment::create($segment);
        }

        // Create default customer tags
        $tags = [
            [
                'name' => 'Wholesale',
                'slug' => 'wholesale',
                'description' => 'Wholesale customers',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Influencer',
                'slug' => 'influencer',
                'description' => 'Social media influencers',
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'name' => 'Corporate',
                'slug' => 'corporate',
                'description' => 'Corporate clients',
                'color' => '#6366F1',
                'is_active' => true,
            ],
            [
                'name' => 'Reseller',
                'slug' => 'reseller',
                'description' => 'Product resellers',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Problematic',
                'slug' => 'problematic',
                'description' => 'Customers with issues or complaints',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Loyal',
                'slug' => 'loyal',
                'description' => 'Long-term loyal customers',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Discount Hunter',
                'slug' => 'discount-hunter',
                'description' => 'Customers who primarily buy on discount',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
        ];

        foreach ($tags as $tag) {
            CustomerTag::create($tag);
        }

        $this->command->info('✅ Customer segments and tags seeded successfully!');
        $this->command->info('   - 7 Customer Segments created');
        $this->command->info('   - 7 Customer Tags created');
    }
}
