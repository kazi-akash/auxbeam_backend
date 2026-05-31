<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Variation;
use App\Models\VariationOption;
use App\Models\VariationValue;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductVariation::truncate();
        VariationValue::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ---------------------------------------------------------------
        // Ensure required Variation types exist
        // ---------------------------------------------------------------
        $sizeVariation = $this->ensureVariation('Size', 'size');
        $bundleVariation = $this->ensureVariation('Bundle', 'bundle');
        $colorVariation = $this->ensureVariation('Housing Color', 'housing-color');
        $quantityVariation = $this->ensureVariation('Quantity', 'quantity');

        // ---------------------------------------------------------------
        // 6 Modes Series Light Bars — Combo size+shape variants
        // Represented via the Size variation with descriptive labels
        // ---------------------------------------------------------------
        $this->applyVariants(
            skus: ['ZD000655', 'ZD000654', 'ZD000653', 'ZD000652', 'ZD000650'],
            variation: $sizeVariation,
            optionMap: [
                'ZD000655' => ['label' => '22" Straight — 120W', 'price_modifier' => 0,     'default' => false],
                'ZD000654' => ['label' => '30" Straight — 180W', 'price_modifier' => 30.99,  'default' => false],
                'ZD000653' => ['label' => '42" Curved — 240W',   'price_modifier' => 50.99,  'default' => false],
                'ZD000652' => ['label' => '50" Curved — 288W',   'price_modifier' => 70.99,  'default' => false],
                'ZD000650' => ['label' => '3" Straight — 72W',   'price_modifier' => -39.01, 'default' => false],
            ]
        );

        // ---------------------------------------------------------------
        // Starlight Flow Headlights — Housing Color variants (Silver / Black)
        // ---------------------------------------------------------------
        $silverProduct = Product::where('sku', 'QP010571')->first();
        $blackProduct  = Product::where('sku', 'QP010907')->first();

        foreach ([
            ['product' => $silverProduct, 'color' => 'Silver', 'price' => 185.99, 'default' => true],
            ['product' => $blackProduct,  'color' => 'Black',  'price' => 199.99, 'default' => false],
        ] as $variant) {
            if (!$variant['product']) continue;
            $option = $this->ensureOption($colorVariation, $variant['color']);
            $pv = ProductVariation::create([
                'product_id' => $variant['product']->id,
                'sku'        => $variant['product']->sku . '-' . strtolower($variant['color']),
                'price'      => $variant['price'],
                'quantity'   => $variant['product']->quantity,
                'is_default' => $variant['default'],
            ]);
            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);
            $variant['product']->update(['quantity' => 0]);
        }

        // ---------------------------------------------------------------
        // RGB Rock Lights — Quantity variants (4pcs / 8pcs / 12pcs)
        // ---------------------------------------------------------------
        $rockLightVariants = [
            ['sku' => 'QP002085', 'label' => '4pcs', 'price' => 62.99,  'qty' => 80,  'default' => true],
            ['sku' => 'QP002314', 'label' => '8pcs', 'price' => 91.99,  'qty' => 65,  'default' => false],
            ['sku' => 'QP009337', 'label' => '12pcs','price' => 115.99, 'qty' => 50,  'default' => false],
        ];

        foreach ($rockLightVariants as $v) {
            $product = Product::where('sku', $v['sku'])->first();
            if (!$product) continue;
            $option = $this->ensureOption($quantityVariation, $v['label']);
            $pv = ProductVariation::create([
                'product_id' => $product->id,
                'sku'        => $product->sku . '-' . strtolower(str_replace('pcs', 'pc', $v['label'])),
                'price'      => $v['price'],
                'quantity'   => $v['qty'],
                'is_default' => $v['default'],
            ]);
            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);
            $product->update(['quantity' => 0]);
        }

        // ---------------------------------------------------------------
        // Vibrant Series Rock Lights — Size + Quantity variants
        // ---------------------------------------------------------------
        $vibrantVariants = [
            ['sku' => 'QP011601', 'label' => 'Large — 4pcs', 'price' => 109.99, 'qty' => 45, 'default' => true],
            ['sku' => 'QP011600', 'label' => 'Large — 8pcs', 'price' => 165.99, 'qty' => 35, 'default' => false],
        ];

        foreach ($vibrantVariants as $v) {
            $product = Product::where('sku', $v['sku'])->first();
            if (!$product) continue;
            $option = $this->ensureOption($sizeVariation, $v['label']);
            $pv = ProductVariation::create([
                'product_id' => $product->id,
                'sku'        => $product->sku . '-' . \Illuminate\Support\Str::slug($v['label']),
                'price'      => $v['price'],
                'quantity'   => $v['qty'],
                'is_default' => $v['default'],
            ]);
            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);
            $product->update(['quantity' => 0]);
        }

        // ---------------------------------------------------------------
        // AR Series Switch Panels — Bundle variants (panel + accessories)
        // ---------------------------------------------------------------
        $arBundleVariants = [
            // 6-Gang bundles
            ['product_sku' => 'QP009889', 'label' => '6 Gang — Panel Only',              'price' => 239.99, 'sku_suffix' => 'panel-only',    'default' => true],
            ['product_sku' => 'QP009889', 'label' => '6 Gang + 3.94FT Extension Cable',  'price' => 255.99, 'sku_suffix' => 'cable-394ft',   'default' => false],
            ['product_sku' => 'QP009889', 'label' => '6 Gang + 20FT Extension Cable',    'price' => 265.99, 'sku_suffix' => 'cable-20ft',    'default' => false],
            // 8-Gang bundles
            ['product_sku' => 'QP010785', 'label' => '8 Gang — Panel Only',              'price' => 269.99, 'sku_suffix' => 'panel-only',    'default' => true],
            ['product_sku' => 'QP010785', 'label' => '8 Gang + 3.94FT Extension Cable',  'price' => 285.99, 'sku_suffix' => 'cable-394ft',   'default' => false],
            ['product_sku' => 'QP010785', 'label' => '8 Gang + 20FT Extension Cable',    'price' => 299.99, 'sku_suffix' => 'cable-20ft',    'default' => false],
        ];

        foreach ($arBundleVariants as $v) {
            $product = Product::where('sku', $v['product_sku'])->first();
            if (!$product) continue;
            $option = $this->ensureOption($bundleVariation, $v['label']);
            $pv = ProductVariation::create([
                'product_id' => $product->id,
                'sku'        => $v['product_sku'] . '-' . $v['sku_suffix'],
                'price'      => $v['price'],
                'quantity'   => $v['default'] ? $product->quantity : rand(10, 25),
                'is_default' => $v['default'],
            ]);
            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);
            if ($v['default']) {
                $product->update(['quantity' => 0]);
            }
        }

        // ---------------------------------------------------------------
        // KS-80 Switch Panel — Bundle variants
        // ---------------------------------------------------------------
        $ks80Variants = [
            ['label' => 'KS-80 Panel Only',               'price' => 299.99, 'sku' => 'QP012186',    'default' => true],
            ['label' => 'KS-80 + Remote Controller',      'price' => 334.99, 'sku' => 'GP00013970',  'default' => false],
            ['label' => 'KS-80 + 3.94FT Extension Cable', 'price' => 304.99, 'sku' => 'GP00013971',  'default' => false],
            ['label' => 'KS-80 + 20FT Extension Cable',   'price' => 314.99, 'sku' => 'GP00013972',  'default' => false],
        ];

        $ks80Product = Product::where('sku', 'QP012186')->first();
        if ($ks80Product) {
            foreach ($ks80Variants as $v) {
                $option = $this->ensureOption($bundleVariation, $v['label']);
                $pv = ProductVariation::create([
                    'product_id' => $ks80Product->id,
                    'sku'        => $v['sku'],
                    'price'      => $v['price'],
                    'quantity'   => $v['default'] ? $ks80Product->quantity : rand(10, 20),
                    'is_default' => $v['default'],
                ]);
                VariationValue::create([
                    'product_variation_id' => $pv->id,
                    'variation_option_id'  => $option->id,
                ]);
            }
            $ks80Product->update(['quantity' => 0]);
        }

        // ---------------------------------------------------------------
        // RGB Whip Lights — Size variants
        // ---------------------------------------------------------------
        $whipVariants = [
            ['sku' => 'QP010354', 'label' => '2.8FT (31mm×850mm)',  'price' => 189.99, 'default' => true],
            ['sku' => 'QP010355', 'label' => '3.8FT (31mm×1150mm)', 'price' => 229.99, 'default' => false],
        ];

        foreach ($whipVariants as $v) {
            $product = Product::where('sku', $v['sku'])->first();
            if (!$product) continue;
            $option = $this->ensureOption($sizeVariation, $v['label']);
            $pv = ProductVariation::create([
                'product_id' => $product->id,
                'sku'        => $product->sku . '-pair',
                'price'      => $v['price'],
                'quantity'   => $product->quantity,
                'is_default' => $v['default'],
            ]);
            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);
            $product->update(['quantity' => 0]);
        }

        $this->command->info('Product variations seeded successfully!');
    }

    private function ensureVariation(string $name, string $slug): Variation
    {
        return Variation::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }

    private function ensureOption(Variation $variation, string $value): VariationOption
    {
        return VariationOption::firstOrCreate(
            ['variation_id' => $variation->id, 'value' => $value],
            ['label' => $value, 'sort_order' => 0]
        );
    }

    private function applyVariants(array $skus, Variation $variation, array $optionMap): void
    {
        foreach ($skus as $sku) {
            $product = Product::where('sku', $sku)->first();
            if (!$product || !isset($optionMap[$sku])) continue;

            $map    = $optionMap[$sku];
            $option = $this->ensureOption($variation, $map['label']);
            $price  = $product->price + ($map['price_modifier'] ?? 0);

            $pv = ProductVariation::create([
                'product_id' => $product->id,
                'sku'        => $sku . '-var',
                'price'      => round($price, 2),
                'quantity'   => $product->quantity,
                'is_default' => $map['default'] ?? false,
            ]);

            VariationValue::create([
                'product_variation_id' => $pv->id,
                'variation_option_id'  => $option->id,
            ]);

            $product->update(['quantity' => 0]);
        }
    }
}
