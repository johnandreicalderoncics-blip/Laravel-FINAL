<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Sample product categories.
     */
    public function run(): void
    {
        $categories = [
            'Electronics' => 'Computer accessories and small gadgets',
            'Home & Kitchen' => 'Drinkware, cookware and everyday home items',
            'School & Office' => 'Notebooks, pens and desk supplies',
            'Apparel' => 'Clothing and wearable accessories',
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['category_name' => $name], ['description' => $description]);
        }
    }
}
