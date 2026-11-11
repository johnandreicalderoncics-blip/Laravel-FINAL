<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Database-level input rules: prices can't be negative and ordered
     * quantities must be at least 1. Stock columns are already unsigned.
     *
     * @var array<string, array<string, string>>
     */
    private array $constraints = [
        'products' => [
            'chk_products_price' => 'price >= 0',
        ],
        'orders' => [
            'chk_orders_total_price' => 'total_price >= 0',
        ],
        'order_items' => [
            'chk_order_items_quantity' => 'quantity >= 1',
            'chk_order_items_item_price' => 'item_price >= 0',
        ],
        'cart_items' => [
            'chk_cart_items_quantity' => 'quantity >= 1',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        foreach ($this->constraints as $table => $checks) {
            foreach ($checks as $name => $expression) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsCheckConstraints()) {
            return;
        }

        foreach ($this->constraints as $table => $checks) {
            foreach (array_keys($checks) as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$name}");
            }
        }
    }

    /**
     * SQLite (used by the test suite) can't add a CHECK to an existing table.
     */
    private function supportsCheckConstraints(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true);
    }
};
