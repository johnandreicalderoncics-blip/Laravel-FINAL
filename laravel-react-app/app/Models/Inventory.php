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

    /**
     * Keep the product's available_quantity in step with the stock count.
     */
    protected static function booted(): void
    {
        static::created(function (Inventory $inventory): void {
            $inventory->syncProduct();
        });

        static::updated(function (Inventory $inventory): void {
            if ($inventory->wasChanged('quantity_in_stock')) {
                $inventory->syncProduct();
            }
        });
    }

    /**
     * Copy quantity_in_stock into the product's available_quantity.
     */
    public function syncProduct(): void
    {
        $product = $this->product()->first();

        if ($product !== null && $product->available_quantity !== $this->quantity_in_stock) {
            $product->forceFill(['available_quantity' => $this->quantity_in_stock])->saveQuietly();
        }
    }

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

    /**
     * Validation rules for the stock update form.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'quantity_in_stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
