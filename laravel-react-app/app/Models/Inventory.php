<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $primaryKey = 'inventory_id';

    public $timestamps = false;

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
     * Update stock quantity and timestamp
     */
    public function updateStock(int $quantity): void
    {
        $this->update([
            'quantity_in_stock' => $quantity,
            'last_stock_update' => now(),
        ]);
    }
}
