<?php

namespace Database\Seeders;

use App\Models\ShippingClass;
use Illuminate\Database\Seeder;

class ShippingClassSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ShippingClass::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $classes = [
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Standard shipping for small LED accessories and bulbs (under 1 lb)',
            ],
            [
                'name' => 'Light Bar',
                'slug' => 'light-bar',
                'description' => 'For LED light bars and larger off-road lighting products',
            ],
            [
                'name' => 'Fragile',
                'slug' => 'fragile',
                'description' => 'For delicate LED headlight assemblies requiring careful handling',
            ],
            [
                'name' => 'Oversized',
                'slug' => 'oversized',
                'description' => 'For large light bars (40"+) and oversized lighting kits',
            ],
        ];

        foreach ($classes as $class) {
            ShippingClass::create($class);
        }

        $this->command->info('Shipping classes seeded successfully!');
    }
}
