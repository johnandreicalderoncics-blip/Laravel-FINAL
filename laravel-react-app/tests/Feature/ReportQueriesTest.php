<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ReportQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_sales_adds_up_completed_orders_only(): void
    {
        Order::factory()->completed()->create(['total_price' => 100.50, 'order_date' => '2026-11-01']);
        Order::factory()->completed()->create(['total_price' => 200.25, 'order_date' => '2026-11-10']);
        Order::factory()->create(['total_price' => 999, 'order_date' => '2026-11-10']);
        Order::factory()->cancelled()->create(['total_price' => 999, 'order_date' => '2026-11-10']);

        $this->assertSame(300.75, Order::totalSales());
        $this->assertSame(200.25, Order::totalSales('2026-11-05'));
        $this->assertSame(100.50, Order::totalSales('2026-11-01', '2026-11-09'));
        $this->assertSame(0.0, Order::totalSales('2027-01-01'));
    }

    public function test_low_stock_lists_active_products_at_or_below_their_reorder_level(): void
    {
        $low = Product::factory()->create(['available_quantity' => 3, 'reorder_level' => 10]);
        $atLevel = Product::factory()->create(['available_quantity' => 10, 'reorder_level' => 10]);
        Product::factory()->create(['available_quantity' => 11, 'reorder_level' => 10]);
        Product::factory()->inactive()->lowStock()->create();

        $lowStock = Product::lowStock()->get();

        $this->assertSame([$low->product_id, $atLevel->product_id], $lowStock->pluck('product_id')->all());
        $this->assertTrue($low->isLowStock());
    }

    public function test_recent_orders_come_newest_first(): void
    {
        $oldest = Order::factory()->create(['order_date' => '2026-11-01']);
        $newest = Order::factory()->create(['order_date' => '2026-11-10']);
        $middle = Order::factory()->create(['order_date' => '2026-11-05']);
        $sameDayLater = Order::factory()->create(['order_date' => '2026-11-10']);

        $this->assertSame(
            [$sameDayLater->order_id, $newest->order_id, $middle->order_id, $oldest->order_id],
            Order::recent()->pluck('order_id')->all(),
        );
        $this->assertCount(2, Order::recent()->limit(2)->get());
    }

    public function test_best_sellers_rank_products_by_units_sold_in_completed_orders(): void
    {
        $completed = Order::factory()->completed()->create();
        $pending = Order::factory()->create();
        $mouse = Product::factory()->create();
        $mug = Product::factory()->create();

        OrderItem::factory()->create(['order_id' => $completed->order_id, 'product_id' => $mouse->product_id, 'quantity' => 2, 'item_price' => 100]);
        OrderItem::factory()->create(['order_id' => $completed->order_id, 'product_id' => $mug->product_id, 'quantity' => 5, 'item_price' => 10]);
        OrderItem::factory()->create(['order_id' => $pending->order_id, 'product_id' => $mouse->product_id, 'quantity' => 50, 'item_price' => 100]);

        $bestSellers = Product::bestSellers();

        $this->assertSame([$mug->product_id, $mouse->product_id], $bestSellers->pluck('product_id')->all());
        $this->assertEquals(5, $bestSellers[0]->units_sold);
        $this->assertEquals(200, $bestSellers[1]->revenue);
    }

    public function test_daily_sales_and_status_counts_summarise_orders(): void
    {
        Order::factory()->completed()->count(2)->create(['total_price' => 50, 'order_date' => now()->toDateString()]);
        Order::factory()->completed()->create(['total_price' => 70, 'order_date' => now()->subDays(2)->toDateString()]);
        Order::factory()->completed()->create(['total_price' => 500, 'order_date' => now()->subDays(30)->toDateString()]);
        Order::factory()->create();

        $dailySales = Order::dailySales(7);

        $this->assertCount(2, $dailySales);
        $this->assertEquals(2, $dailySales[0]->orders_count);
        $this->assertEquals(100, $dailySales[0]->sales);
        $this->assertEquals(70, $dailySales[1]->sales);
        $this->assertSame(['completed' => 4, 'pending' => 1], Order::countByStatus()->sortKeys()->all());
    }

    public function test_recalculate_total_sums_the_order_items(): void
    {
        $order = Order::factory()->create(['total_price' => 0]);
        OrderItem::factory()->create(['order_id' => $order->order_id, 'quantity' => 2, 'item_price' => 49.50]);
        OrderItem::factory()->create(['order_id' => $order->order_id, 'quantity' => 1, 'item_price' => 1.25]);

        $order->recalculateTotal();

        $this->assertSame('100.25', $order->refresh()->total_price);
    }

    public function test_product_and_inventory_stock_stay_in_step(): void
    {
        $product = Product::factory()->create(['available_quantity' => 20]);
        $this->assertSame(20, $product->inventory->quantity_in_stock);

        $product->update(['available_quantity' => 15]);
        $this->assertSame(15, $product->inventory()->first()->quantity_in_stock);

        $product->decrement('available_quantity', 5);
        $this->assertSame(10, $product->inventory()->first()->quantity_in_stock);

        $product->inventory()->first()->updateStock(40);
        $this->assertSame(40, $product->refresh()->available_quantity);

        $this->assertDatabaseCount('inventories', 1);
    }

    public function test_adjust_stock_changes_stock_and_refuses_to_go_below_zero(): void
    {
        $product = Product::factory()->create(['available_quantity' => 5]);

        $product->adjustStock(-3);
        $this->assertSame(2, $product->available_quantity);
        $this->assertSame(2, $product->inventory()->first()->quantity_in_stock);

        $product->adjustStock(10);
        $this->assertSame(12, $product->available_quantity);

        try {
            $product->adjustStock(-13);
            $this->fail('Stock was allowed to go below zero.');
        } catch (RuntimeException) {
            $this->assertSame(12, $product->refresh()->available_quantity);
        }
    }
}
