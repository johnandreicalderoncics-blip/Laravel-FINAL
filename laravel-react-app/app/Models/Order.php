<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'user_id',
        'order_date',
        'total_price',
        'order_status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    /**
     * Recompute total_price from the order's line items.
     */
    public function recalculateTotal(): void
    {
        $total = $this->orderItems()
            ->selectRaw('COALESCE(SUM(quantity * item_price), 0) as total')
            ->value('total');

        $this->update(['total_price' => $total]);
    }
}
