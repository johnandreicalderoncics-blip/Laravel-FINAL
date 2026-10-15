<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['product_name' => 'Espresso', 'description' => 'Double shot espresso', 'price' => 90.00, 'category' => 'Drinks', 'stock' => 100],
            ['product_name' => 'Iced Latte', 'description' => '16oz iced latte', 'price' => 130.00, 'category' => 'Drinks', 'stock' => 80],
            ['product_name' => 'Chicken Sandwich', 'description' => 'Grilled chicken on ciabatta', 'price' => 185.00, 'category' => 'Food', 'stock' => 40],
            ['product_name' => 'Chocolate Muffin', 'description' => 'Freshly baked muffin', 'price' => 75.00, 'category' => 'Pastry', 'stock' => 60],
        ];

        foreach ($products as $data) {
            $stock = $data['stock'];
            unset($data['stock']);

            $product = Product::create($data + ['available_quantity' => $stock]);

            Inventory::create([
                'product_id' => $product->product_id,
                'quantity_in_stock' => $stock,
            ]);
        }
    }
}
