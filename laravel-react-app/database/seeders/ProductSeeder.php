<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Sample products with one inventory row each. A few are at or below
     * their reorder level, one is out of stock and one is inactive, so the
     * low-stock and status screens have something to show.
     */
    public function run(): void
    {
        $products = [
            'Electronics' => [
                ['Wireless Mouse', '2.4 GHz optical mouse with USB receiver', 499.00, 45, 10, Product::STATUS_ACTIVE],
                ['Mechanical Keyboard', 'Full-size keyboard with blue switches', 1899.00, 8, 10, Product::STATUS_ACTIVE],
                ['USB-C Charging Cable 1m', 'Braided fast-charging cable', 199.00, 120, 20, Product::STATUS_ACTIVE],
                ['Bluetooth Earbuds', 'True wireless earbuds with charging case', 1299.00, 0, 5, Product::STATUS_ACTIVE],
            ],
            'Home & Kitchen' => [
                ['Stainless Steel Tumbler 500ml', 'Double-wall insulated tumbler', 349.00, 60, 15, Product::STATUS_ACTIVE],
                ['Ceramic Coffee Mug', '350ml glazed ceramic mug', 149.00, 12, 15, Product::STATUS_ACTIVE],
                ['Non-stick Frying Pan 24cm', 'Aluminium pan with heat-resistant handle', 799.00, 25, 5, Product::STATUS_ACTIVE],
            ],
            'School & Office' => [
                ['Spiral Notebook A5', '80 ruled leaves', 59.00, 200, 30, Product::STATUS_ACTIVE],
                ['Gel Pen Set (10 pcs)', '0.5mm assorted colors', 129.00, 5, 20, Product::STATUS_ACTIVE],
                ['Desk Organizer', 'Five-compartment mesh organizer', 279.00, 30, 10, Product::STATUS_ACTIVE],
            ],
            'Apparel' => [
                ['Cotton T-Shirt (Plain White)', 'Unisex round-neck shirt, 100% cotton', 249.00, 75, 15, Product::STATUS_ACTIVE],
                ['Canvas Tote Bag', 'Heavy-duty canvas bag with inner pocket', 199.00, 40, 10, Product::STATUS_ACTIVE],
                ['Baseball Cap', 'Adjustable cap, discontinued design', 229.00, 20, 10, Product::STATUS_INACTIVE],
            ],
        ];

        foreach ($products as $categoryName => $rows) {
            $category = Category::where('category_name', $categoryName)->firstOrFail();

            foreach ($rows as [$name, $description, $price, $quantity, $reorderLevel, $status]) {
                $product = Product::firstOrCreate(
                    ['product_name' => $name],
                    [
                        'category_id' => $category->category_id,
                        'description' => $description,
                        'price' => $price,
                        'available_quantity' => $quantity,
                        'reorder_level' => $reorderLevel,
                        'status' => $status,
                    ],
                );

                Inventory::firstOrCreate(
                    ['product_id' => $product->product_id],
                    ['quantity_in_stock' => $product->available_quantity, 'last_stock_update' => now()],
                );
            }
        }
    }
}
