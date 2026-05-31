<?php

namespace Database\Seeders;

use App\Models\ShippingClass;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ShippingRate::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $standardClass = ShippingClass::where('slug', 'standard')->first();
        $lightBarClass = ShippingClass::where('slug', 'light-bar')->first();
        $fragileClass = ShippingClass::where('slug', 'fragile')->first();
        $oversizedClass = ShippingClass::where('slug', 'oversized')->first();

        // US Standard Shipping — free over $19.99
        ShippingRate::create([
            'name' => 'US Standard Shipping',
            'shipping_class_id' => $standardClass?->id,
            'method' => 'standard_shipping',
            'country' => 'US',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 19.99,
            'base_cost' => 5.99,
            'is_active' => true,
        ]);

        // US Light Bar Shipping — free over $19.99
        ShippingRate::create([
            'name' => 'US Light Bar Shipping',
            'shipping_class_id' => $lightBarClass?->id,
            'method' => 'standard_shipping',
            'country' => 'US',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 19.99,
            'base_cost' => 9.99,
            'is_active' => true,
        ]);

        // US Fragile / Headlight Shipping
        ShippingRate::create([
            'name' => 'US Fragile Item Shipping',
            'shipping_class_id' => $fragileClass?->id,
            'method' => 'standard_shipping',
            'country' => 'US',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 19.99,
            'base_cost' => 7.99,
            'is_active' => true,
        ]);

        // US Oversized Shipping (large 40"+ light bars)
        ShippingRate::create([
            'name' => 'US Oversized Shipping',
            'shipping_class_id' => $oversizedClass?->id,
            'method' => 'standard_shipping',
            'country' => 'US',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 19.99,
            'base_cost' => 14.99,
            'is_active' => true,
        ]);

        // International Standard Shipping
        ShippingRate::create([
            'name' => 'International Standard Shipping',
            'shipping_class_id' => $standardClass?->id,
            'method' => 'international_shipping',
            'country' => 'Worldwide',
            'delivery_time' => '8-15 business days',
            'free_shipping_min_order' => 0,
            'base_cost' => 19.99,
            'is_active' => true,
        ]);

        // International Light Bar Shipping
        ShippingRate::create([
            'name' => 'International Light Bar Shipping',
            'shipping_class_id' => $lightBarClass?->id,
            'method' => 'international_shipping',
            'country' => 'Worldwide',
            'delivery_time' => '8-15 business days',
            'free_shipping_min_order' => 0,
            'base_cost' => 29.99,
            'is_active' => true,
        ]);
    }
}
