<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Sample orders spread over the last two weeks in every status, plus a
     * small cart. Skipped when orders already exist.
     */
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $orders = [
            ['customer1@example.com', 12, Order::STATUS_COMPLETED, 'cash_on_delivery', ['Wireless Mouse' => 1, 'USB-C Charging Cable 1m' => 2]],
            ['customer2@example.com', 9, Order::STATUS_COMPLETED, 'gcash', ['Stainless Steel Tumbler 500ml' => 2, 'Ceramic Coffee Mug' => 4]],
            ['customer1@example.com', 6, Order::STATUS_COMPLETED, 'card', ['Mechanical Keyboard' => 1]],
            ['customer2@example.com', 4, Order::STATUS_CANCELLED, 'cash_on_delivery', ['Bluetooth Earbuds' => 1]],
            ['customer1@example.com', 3, Order::STATUS_COMPLETED, 'gcash', ['Spiral Notebook A5' => 5, 'Gel Pen Set (10 pcs)' => 2, 'Desk Organizer' => 1]],
            ['customer2@example.com', 1, Order::STATUS_PROCESSING, 'bank_transfer', ['Cotton T-Shirt (Plain White)' => 3, 'Canvas Tote Bag' => 1]],
            ['customer1@example.com', 0, Order::STATUS_PENDING, 'cash_on_delivery', ['Non-stick Frying Pan 24cm' => 1, 'Ceramic Coffee Mug' => 2]],
            ['customer2@example.com', 0, Order::STATUS_PENDING, 'gcash', ['Wireless Mouse' => 1]],
        ];

        foreach ($orders as [$email, $daysAgo, $status, $paymentMethod, $items]) {
            $customer = User::where('email', $email)->firstOrFail();
            $placedAt = now()->subDays($daysAgo);

            $order = new Order([
                'user_id' => $customer->user_id,
                'order_date' => $placedAt->toDateString(),
                'order_status' => $status,
                'shipping_address' => $customer->address,
                'contact_number' => $customer->phone_number,
                'payment_method' => $paymentMethod,
            ]);
            $order->created_at = $placedAt;
            $order->updated_at = $placedAt;
            $order->save();

            foreach ($items as $productName => $quantity) {
                $product = Product::where('product_name', $productName)->firstOrFail();

                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'quantity' => $quantity,
                    'item_price' => $product->price,
                ]);
            }

            $order->recalculateTotal();
        }

        $cartOwner = User::where('email', 'customer1@example.com')->firstOrFail();

        foreach (['Canvas Tote Bag' => 1, 'Spiral Notebook A5' => 3] as $productName => $quantity) {
            CartItem::create([
                'user_id' => $cartOwner->user_id,
                'product_id' => Product::where('product_name', $productName)->firstOrFail()->product_id,
                'quantity' => $quantity,
            ]);
        }
    }
}
