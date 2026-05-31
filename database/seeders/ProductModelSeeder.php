<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductModel::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $models = [
            'Auxbeam' => [
                '5D-PRO Series',
                '6 Modes Series',
                'V-PRO Series',
                'GX-Ultra Series',
                'GX-Bi Color Series',
                'Starlight Flow Series',
                'AR Series',
                'KS-80 Series',
                'Vibrant Series',
                'HID Replacement Series',
            ],
        ];

        foreach ($models as $brandName => $modelNames) {
            $brand = Brand::where('name', $brandName)->first();
            if ($brand) {
                foreach ($modelNames as $modelName) {
                    ProductModel::create([
                        'brand_id' => $brand->id,
                        'name' => $modelName,
                    ]);
                }
            }
        }

        $this->command->info('Product models seeded successfully!');
    }
}
