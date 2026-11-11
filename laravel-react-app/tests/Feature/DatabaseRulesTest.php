<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DatabaseRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['email' => 'taken@example.com']);
    }

    public function test_user_rules_require_a_unique_email_but_let_a_user_keep_their_own(): void
    {
        $user = User::factory()->create(['email' => 'taken@example.com']);
        $input = ['name' => 'Someone', 'email' => 'taken@example.com', 'password' => 'password123'];

        $this->assertTrue(Validator::make($input, User::rules())->errors()->has('email'));
        $this->assertFalse(Validator::make($input, User::rules($user->user_id))->errors()->has('email'));
    }

    public function test_product_rules_reject_negative_price_and_quantity(): void
    {
        $input = [
            'product_name' => 'Bad Product',
            'price' => -1,
            'available_quantity' => -5,
            'reorder_level' => -2,
            'status' => 'active',
        ];

        $errors = Validator::make($input, Product::rules())->errors();

        $this->assertTrue($errors->has('price'));
        $this->assertTrue($errors->has('available_quantity'));
        $this->assertTrue($errors->has('reorder_level'));
        $this->assertTrue(Validator::make([...$input, 'price' => 0, 'available_quantity' => 0, 'reorder_level' => 0], Product::rules())->passes());
    }

    public function test_quantity_rules_reject_zero_and_negative_quantities(): void
    {
        $product = Product::factory()->create();

        foreach ([0, -3] as $quantity) {
            $input = ['product_id' => $product->product_id, 'quantity' => $quantity, 'item_price' => 10];

            $this->assertTrue(Validator::make($input, CartItem::rules())->errors()->has('quantity'));
            $this->assertTrue(Validator::make($input, OrderItem::rules())->errors()->has('quantity'));
        }

        $this->assertTrue(Validator::make(['quantity_in_stock' => -1], Inventory::rules())->fails());
        $this->assertTrue(Validator::make(['product_id' => $product->product_id, 'quantity' => 1], CartItem::rules())->passes());
    }

    public function test_order_rules_require_shipping_address_contact_number_and_a_known_payment_method(): void
    {
        $errors = Validator::make(['payment_method' => 'bitcoin'], Order::rules())->errors();

        $this->assertTrue($errors->has('shipping_address'));
        $this->assertTrue($errors->has('contact_number'));
        $this->assertTrue($errors->has('payment_method'));
        $this->assertTrue(Validator::make([
            'shipping_address' => '1 Test Street, Test City',
            'contact_number' => '+63 917 123 4567',
            'payment_method' => 'gcash',
        ], Order::rules())->passes());
    }

    public function test_the_database_rejects_negative_prices_and_zero_quantities(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
            $this->markTestSkipped('CHECK constraints are only added on MySQL, MariaDB and PostgreSQL.');
        }

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $badWrites = [
            fn () => DB::table('products')->insert(['product_name' => 'Negative', 'price' => -1]),
            fn () => DB::table('products')->where('product_id', $product->product_id)->update(['available_quantity' => -1]),
            fn () => DB::table('orders')->where('order_id', $order->order_id)->update(['total_price' => -1]),
            fn () => DB::table('order_items')->insert(['order_id' => $order->order_id, 'product_id' => $product->product_id, 'quantity' => 0, 'item_price' => 10]),
            fn () => DB::table('order_items')->insert(['order_id' => $order->order_id, 'product_id' => $product->product_id, 'quantity' => 1, 'item_price' => -10]),
            fn () => DB::table('cart_items')->insert(['user_id' => $order->user_id, 'product_id' => $product->product_id, 'quantity' => 0]),
            fn () => DB::table('inventories')->where('product_id', $product->product_id)->update(['quantity_in_stock' => -1]),
        ];

        foreach ($badWrites as $index => $badWrite) {
            try {
                $badWrite();
                $this->fail("Bad write #{$index} was accepted by the database.");
            } catch (QueryException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_a_user_with_orders_or_logs_cannot_be_deleted(): void
    {
        $customer = Order::factory()->create()->user;
        $staff = User::factory()->employee()->create();
        ActivityLog::record($staff, 'Logged in');

        foreach ([$customer, $staff] as $user) {
            try {
                $user->delete();
                $this->fail('A user with history was deleted.');
            } catch (QueryException) {
                $this->assertDatabaseHas('users', ['user_id' => $user->user_id]);
            }
        }
    }

    public function test_deleting_a_user_without_history_clears_their_cart(): void
    {
        $cartItem = CartItem::factory()->create();

        $cartItem->user->delete();

        $this->assertDatabaseMissing('cart_items', ['cart_item_id' => $cartItem->cart_item_id]);
        $this->assertDatabaseHas('products', ['product_id' => $cartItem->product_id]);
    }

    public function test_deleting_an_order_deletes_its_items_but_keeps_the_products(): void
    {
        $orderItem = OrderItem::factory()->create();

        $orderItem->order->delete();

        $this->assertDatabaseMissing('order_items', ['order_item_id' => $orderItem->order_item_id]);
        $this->assertDatabaseHas('products', ['product_id' => $orderItem->product_id]);
    }

    public function test_a_product_that_has_been_ordered_cannot_be_deleted(): void
    {
        $product = OrderItem::factory()->create()->product;

        try {
            $product->delete();
            $this->fail('An ordered product was deleted.');
        } catch (QueryException) {
            $this->assertDatabaseHas('products', ['product_id' => $product->product_id]);
        }
    }

    public function test_deleting_an_unordered_product_removes_its_inventory_and_cart_rows(): void
    {
        $cartItem = CartItem::factory()->create();
        $product = $cartItem->product;

        $this->assertDatabaseHas('inventories', ['product_id' => $product->product_id]);

        $product->delete();

        $this->assertDatabaseMissing('inventories', ['product_id' => $product->product_id]);
        $this->assertDatabaseMissing('cart_items', ['cart_item_id' => $cartItem->cart_item_id]);
    }

    public function test_a_category_that_still_has_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->category_id]);

        try {
            $category->delete();
            $this->fail('A category with products was deleted.');
        } catch (QueryException) {
            $this->assertDatabaseHas('categories', ['category_id' => $category->category_id]);
        }
    }

    public function test_a_product_can_only_appear_once_per_cart_and_once_per_order(): void
    {
        $cartItem = CartItem::factory()->create();
        $orderItem = OrderItem::factory()->create();

        try {
            CartItem::factory()->create(['user_id' => $cartItem->user_id, 'product_id' => $cartItem->product_id]);
            $this->fail('A duplicate cart row was accepted.');
        } catch (UniqueConstraintViolationException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(UniqueConstraintViolationException::class);

        OrderItem::factory()->create(['order_id' => $orderItem->order_id, 'product_id' => $orderItem->product_id]);
    }
}
