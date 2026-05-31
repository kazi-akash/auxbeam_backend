<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Brand::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $brands = [
            [
                'name' => 'Auxbeam',
                'slug' => 'auxbeam',
                'image_file' => 'auxbeam.png',
                'description' => 'Auxbeam Lighting — premium automotive LED lighting manufacturer specializing in LED light bars, headlights, and off-road lights.',
                'sort_order' => 1,
            ],
        ];

        $sourceDir = public_path('images/all-brands');
        $storageDir = storage_path('app/public/brands');

        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        foreach ($brands as $brandData) {
            $logoPath = null;

            if (isset($brandData['image_file'])) {
                $sourceFile = $sourceDir . '/' . $brandData['image_file'];

                if (File::exists($sourceFile)) {
                    $destinationFile = $storageDir . '/' . $brandData['image_file'];
                    File::copy($sourceFile, $destinationFile);
                    $logoPath = 'brands/' . $brandData['image_file'];
                }
            }

            Brand::create([
                'name' => $brandData['name'],
                'slug' => $brandData['slug'],
                'logo' => $logoPath,
                'description' => $brandData['description'],
                'sort_order' => $brandData['sort_order'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Brands seeded successfully!');
    }
}
