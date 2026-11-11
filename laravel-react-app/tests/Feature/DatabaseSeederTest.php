<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_seeder_creates_an_admin_who_can_log_in(): void
    {
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@example.com')->sole();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertTrue(Auth::attempt(['email' => 'admin@example.com', 'password' => 'password']));
        $this->assertFalse(Auth::attempt(['email' => 'admin@example.com', 'password' => 'wrong-password']));
    }

    public function test_the_database_seeder_fills_every_table_and_can_be_run_twice(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('products', 13);
        $this->assertDatabaseCount('inventories', 13);
        $this->assertDatabaseCount('orders', 8);
        $this->assertDatabaseCount('order_items', 14);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseCount('logs', 11);
    }

    public function test_the_sample_data_gives_the_shared_queries_something_to_show(): void
    {
        $this->seed();

        $this->assertSame(4922.0, Order::totalSales());
        $this->assertSame(
            ['Bluetooth Earbuds', 'Gel Pen Set (10 pcs)', 'Mechanical Keyboard', 'Ceramic Coffee Mug'],
            Product::lowStock()->pluck('product_name')->all(),
        );
        $this->assertSame(Order::max('order_id'), Order::recent()->first()->order_id);
        $this->assertTrue(Order::recent()->first()->order_date->isToday());
        $this->assertSame('Spiral Notebook A5', Product::bestSellers(1)->sole()->product_name);
        $this->assertSame(
            0,
            Product::query()
                ->join('inventories', 'inventories.product_id', '=', 'products.product_id')
                ->whereColumn('products.available_quantity', '!=', 'inventories.quantity_in_stock')
                ->count(),
        );
    }
}
