<?php

namespace Tests\Feature;

use App\Models\Inventory;
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
            'user_role' => User::ROLE_CUSTOMER,
        ]);

        $product = Product::create([
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
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'item_price' => 12.50,
        ]);

        $inventory = Inventory::create([
            'product_id' => $product->product_id,
            'quantity_in_stock' => 5,
            'last_stock_update' => now(),
        ]);

        $this->assertSame($user->user_id, $order->user->user_id);
        $this->assertSame($order->order_id, $user->orders->sole()->order_id);
        $this->assertSame($product->product_id, $orderItem->product->product_id);
        $this->assertSame($order->order_id, $orderItem->order->order_id);
        $this->assertSame($orderItem->order_item_id, $product->orderItems->sole()->order_item_id);
        $this->assertSame($inventory->inventory_id, $product->inventory->sole()->inventory_id);
        $this->assertSame($product->product_id, $inventory->product->product_id);
    }
}
