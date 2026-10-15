<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    protected $primaryKey = 'inventory_id';
    
    protected $fillable = [
        'product_id',
        'quantity_in_stock',
        'last_stock_update',
    ];

    protected $casts = [
        'quantity_in_stock' => 'integer',
        'last_stock_update' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Update stock quantity and timestamp.
     *
     * Inventory is the source of truth; products.available_quantity is kept
     * in sync inside the same transaction so the two never drift apart.
     */
    public function updateStock(int $quantity): void
    {
        DB::transaction(function () use ($quantity) {
            $this->update([
                'quantity_in_stock' => $quantity,
                'last_stock_update' => now(),
            ]);

            Product::whereKey($this->product_id)->update(['available_quantity' => $quantity]);
        });
    }
}
