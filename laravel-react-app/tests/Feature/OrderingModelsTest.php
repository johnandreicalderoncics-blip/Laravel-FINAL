<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderingModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordering_models_persist_and_resolve_their_relationships(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $category = Category::create([
            'category_name' => 'Test Category',
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Test Product',
            'description' => 'Product for relationship test',
            'price' => 12.50,
            'available_quantity' => 5,
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'order_date' => '2026-10-15',
            'total_price' => 12.50,
            'order_status' => 'pending',
            'shipping_address' => '1 Test Street, Test City',
            'contact_number' => '09171234567',
            'payment_method' => 'cash_on_delivery',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'item_price' => 12.50,
        ]);

        $cartItem = CartItem::create([
            'user_id' => $user->user_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
        ]);

        $log = ActivityLog::record($user, 'Placed order');

        $inventory = $product->inventory;

        $this->assertSame($user->user_id, $order->user->user_id);
        $this->assertSame($order->order_id, $user->orders->sole()->order_id);
        $this->assertSame($product->product_id, $orderItem->product->product_id);
        $this->assertSame($order->order_id, $orderItem->order->order_id);
        $this->assertSame($orderItem->order_item_id, $product->orderItems->sole()->order_item_id);
        $this->assertSame(5, $inventory->quantity_in_stock);
        $this->assertSame($product->product_id, $inventory->product->product_id);
        $this->assertSame($category->category_id, $product->category->category_id);
        $this->assertSame($product->product_id, $category->products->sole()->product_id);
        $this->assertSame($cartItem->cart_item_id, $user->cartItems->sole()->cart_item_id);
        $this->assertSame(25.0, $cartItem->subtotal());
        $this->assertSame($log->log_id, $user->activityLogs->sole()->log_id);
        $this->assertSame($user->user_id, $log->user->user_id);
    }

    public function test_new_users_products_and_orders_get_their_default_values(): void
    {
        $user = User::factory()->create()->refresh();
        $product = Product::create(['product_name' => 'Defaults', 'price' => 10])->refresh();
        $order = Order::create([
            'user_id' => $user->user_id,
            'shipping_address' => '1 Test Street, Test City',
            'contact_number' => '09171234567',
        ])->refresh();

        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue($user->isActive());
        $this->assertTrue($product->isActive());
        $this->assertSame(0, $product->available_quantity);
        $this->assertSame(10, $product->reorder_level);
        $this->assertNull($product->image);
        $this->assertSame(Order::STATUS_PENDING, $order->order_status);
        $this->assertSame('cash_on_delivery', $order->payment_method);
        $this->assertTrue($order->order_date->isToday());
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->assertNotSame('secret-password', $user->getAttributes()['password']);
        $this->assertTrue(password_verify('secret-password', $user->getAttributes()['password']));
    }
}
