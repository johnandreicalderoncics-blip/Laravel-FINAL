<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderingSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10, float $price = 50.00): Product
    {
        $product = Product::create([
            'product_name' => 'Widget',
            'price' => $price,
            'available_quantity' => $stock,
        ]);

        Inventory::create(['product_id' => $product->product_id, 'quantity_in_stock' => $stock]);

        return $product;
    }

    private function makeOrder(): Order
    {
        $user = User::factory()->create();

        return Order::create(['user_id' => $user->user_id, 'order_date' => now()->toDateString()]);
    }

    public function test_order_total_is_recalculated_from_items(): void
    {
        $order = $this->makeOrder();
        $a = $this->makeProduct();
        $b = $this->makeProduct(price: 20.00);

        OrderItem::create(['order_id' => $order->order_id, 'product_id' => $a->product_id, 'quantity' => 2, 'item_price' => 50.00]);
        OrderItem::create(['order_id' => $order->order_id, 'product_id' => $b->product_id, 'quantity' => 1, 'item_price' => 20.00]);

        $order->recalculateTotal();

        $this->assertEquals('120.00', $order->fresh()->total_price);
    }

    public function test_update_stock_keeps_product_quantity_in_sync(): void
    {
        $product = $this->makeProduct(10);

        $product->inventory->updateStock(7);

        $this->assertSame(7, $product->fresh()->available_quantity);
        $this->assertSame(7, $product->fresh()->inventory->quantity_in_stock);
    }

    public function test_product_can_only_have_one_inventory_row(): void
    {
        $product = $this->makeProduct();

        $this->expectException(QueryException::class);
        Inventory::create(['product_id' => $product->product_id, 'quantity_in_stock' => 1]);
    }

    public function test_duplicate_product_lines_in_one_order_are_rejected(): void
    {
        $order = $this->makeOrder();
        $product = $this->makeProduct();
        $line = ['order_id' => $order->order_id, 'product_id' => $product->product_id, 'quantity' => 1, 'item_price' => 50.00];

        OrderItem::create($line);

        $this->expectException(QueryException::class);
        OrderItem::create($line);
    }

    public function test_product_with_order_history_cannot_be_hard_deleted(): void
    {
        $order = $this->makeOrder();
        $product = $this->makeProduct();
        OrderItem::create(['order_id' => $order->order_id, 'product_id' => $product->product_id, 'quantity' => 1, 'item_price' => 50.00]);

        $this->expectException(QueryException::class);
        $product->forceDelete();
    }

    public function test_soft_deleting_a_product_keeps_order_history(): void
    {
        $order = $this->makeOrder();
        $product = $this->makeProduct();
        OrderItem::create(['order_id' => $order->order_id, 'product_id' => $product->product_id, 'quantity' => 1, 'item_price' => 50.00]);

        $product->delete();

        $this->assertSame(1, $order->orderItems()->count());
    }

    public function test_logs_are_recorded_for_a_user(): void
    {
        $user = User::factory()->create();

        Log::create(['user_id' => $user->user_id, 'action' => 'placed order', 'logged_at' => now()]);

        $this->assertSame(1, $user->logs()->count());
    }
}
