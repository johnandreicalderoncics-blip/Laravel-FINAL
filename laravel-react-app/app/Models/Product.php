<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $primaryKey = 'product_id';
    
    protected $fillable = [
        'product_name',
        'description',
        'price',
        'available_quantity',
        'category',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_quantity' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id', 'product_id');
    }
}
