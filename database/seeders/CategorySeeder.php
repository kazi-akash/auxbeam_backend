<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | LED LIGHT BARS
        |--------------------------------------------------------------------------
        */
        $lightBars = $this->createCategory('LED Light Bars', null, 1);
        $lightBarItems = [
            '5D-PRO Series',
            '6 Modes Series',
            'Curved Light Bars',
            'Straight Light Bars',
            'RGBW Light Bars',
            'Combo Beam Light Bars',
        ];
        foreach ($lightBarItems as $index => $item) {
            $this->createCategory($item, $lightBars->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | LED HEADLIGHTS & FOG LIGHTS
        |--------------------------------------------------------------------------
        */
        $headlights = $this->createCategory('LED Headlights & Fog Lights', null, 2);
        $headlightItems = [
            '7 Inch Round Headlights',
            '5.75 Inch Round Headlights',
            'Projector Headlights',
            'LED Fog Lights',
            'DRL Headlights',
            'RGB Halo Headlights',
        ];
        foreach ($headlightItems as $index => $item) {
            $this->createCategory($item, $headlights->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | LED REPLACEMENT BULBS
        |--------------------------------------------------------------------------
        */
        $bulbs = $this->createCategory('LED Replacement Bulbs', null, 3);
        $bulbItems = [
            'H4 / 9003 Bulbs',
            'H7 Bulbs',
            'H11 / H8 / H9 Bulbs',
            '9005 / HB3 Bulbs',
            '9006 / HB4 Bulbs',
            'H13 / 9008 Bulbs',
            'Turn Signal Bulbs',
            'Brake & Tail Light Bulbs',
            'Reversing / Backup Bulbs',
        ];
        foreach ($bulbItems as $index => $item) {
            $this->createCategory($item, $bulbs->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | LED OFF ROAD LIGHTS
        |--------------------------------------------------------------------------
        */
        $offRoad = $this->createCategory('LED Off Road Lights', null, 4);
        $offRoadItems = [
            '7 Inch Off Road Lights',
            '9 Inch Off Road Lights',
            'LED Pod Lights',
            'LED Work Lights',
            'Side Shooter Pods',
            'Mounting Brackets & Accessories',
        ];
        foreach ($offRoadItems as $index => $item) {
            $this->createCategory($item, $offRoad->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | SWITCH PANELS
        |--------------------------------------------------------------------------
        */
        $switchPanels = $this->createCategory('Switch Panels', null, 5);
        $switchItems = [
            'APP Control Switch Panels',
            'Remote Control Switch Panels',
            'On/Off Toggle Switch Panels',
            'Knob Switch Panels',
            'Switch Panel Accessories',
        ];
        foreach ($switchItems as $index => $item) {
            $this->createCategory($item, $switchPanels->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | RGB LED LIGHTS
        |--------------------------------------------------------------------------
        */
        $rgb = $this->createCategory('RGB LED Lights', null, 6);
        $rgbItems = [
            'RGB Rock Lights',
            'RGB Whip Lights',
            'RGB LED Headlights',
            'RGB Interior Lights',
            'RGB Light Bars',
        ];
        foreach ($rgbItems as $index => $item) {
            $this->createCategory($item, $rgb->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | JEEP SPECIFIC
        |--------------------------------------------------------------------------
        */
        $jeep = $this->createCategory('Jeep Specific', null, 7);
        $jeepItems = [
            'Jeep Wrangler JK (2007-2017)',
            'Jeep Wrangler JL (2018+)',
            'Jeep Wrangler TJ (1997-2006)',
            'Jeep Gladiator JT',
            'Jeep Headlights',
            'Jeep Light Bars',
        ];
        foreach ($jeepItems as $index => $item) {
            $this->createCategory($item, $jeep->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | WIRING & ACCESSORIES
        |--------------------------------------------------------------------------
        */
        $accessories = $this->createCategory('Wiring & Accessories', null, 8);
        $accessoryItems = [
            'Wiring Harnesses',
            'DT Connector Harnesses',
            'Switch Labels & Stickers',
            'Extension Cables',
        ];
        foreach ($accessoryItems as $index => $item) {
            $this->createCategory($item, $accessories->id, $index + 1);
        }
    }

    private function createCategory(string $name, ?int $parentId, int $sortOrder): Category
    {
        $slug = Str::slug($name);

        $existingCategory = Category::where('slug', $slug)->first();
        if ($existingCategory) {
            if ($parentId) {
                $parent = Category::find($parentId);
                $slug = Str::slug($parent->name . '-' . $name);
            } else {
                $slug = Str::slug($name . '-' . uniqid());
            }
        }

        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId,
            'description' => $name . ' - automotive LED lighting products',
            'meta_title' => $name . ' | Auxbeam Lighting',
            'meta_description' => 'Shop ' . strtolower($name) . ' at Auxbeam Lighting',
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}
