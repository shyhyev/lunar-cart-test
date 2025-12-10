<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currency = Currency::getDefault();
        $taxClass = TaxClass::first();

        $productType = ProductType::first() ?? ProductType::create([
            'name' => 'Physical Product',
        ]);

        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'sku' => 'WBH-001',
                'price' => 7999,
                'stock' => 50,
            ],
            [
                'name' => 'Smart Fitness Watch',
                'description' => 'Track your fitness goals with this waterproof smartwatch featuring heart rate monitoring and GPS.',
                'sku' => 'SFW-002',
                'price' => 19999,
                'stock' => 30,
            ],
            [
                'name' => 'Portable Power Bank 20000mAh',
                'description' => 'Fast charging power bank with dual USB ports and LED display.',
                'sku' => 'PPB-003',
                'price' => 3499,
                'stock' => 100,
            ],
            [
                'name' => 'USB-C Hub Adapter',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, SD card reader, and more.',
                'sku' => 'UCH-004',
                'price' => 2999,
                'stock' => 75,
            ],
            [
                'name' => 'Mechanical Gaming Keyboard',
                'description' => 'RGB backlit mechanical keyboard with blue switches and programmable keys.',
                'sku' => 'MGK-005',
                'price' => 8999,
                'stock' => 40,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with adjustable DPI and rechargeable battery.',
                'sku' => 'WM-006',
                'price' => 2499,
                'stock' => 80,
            ],
            [
                'name' => 'Laptop Stand',
                'description' => 'Aluminum laptop stand with adjustable height and cable management.',
                'sku' => 'LS-007',
                'price' => 3999,
                'stock' => 60,
            ],
            [
                'name' => 'Webcam 1080p HD',
                'description' => 'Full HD webcam with auto-focus and built-in microphone.',
                'sku' => 'WC-008',
                'price' => 4999,
                'stock' => 45,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'product_type_id' => $productType->id,
                'status' => 'published',
                'attribute_data' => [
                    'name' => new \Lunar\FieldTypes\Text($productData['name']),
                    'description' => new \Lunar\FieldTypes\Text($productData['description']),
                ],
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'sku' => $productData['sku'],
                'stock' => $productData['stock'],
                'purchasable' => 'always',
            ]);

            if ($currency && $taxClass) {
                $variant->prices()->create([
                    'currency_id' => $currency->id,
                    'price' => $productData['price'],
                ]);
            }
        }

        $this->command->info('Created ' . count($products) . ' products successfully!');
    }
}
