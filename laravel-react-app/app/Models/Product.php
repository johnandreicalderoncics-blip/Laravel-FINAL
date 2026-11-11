<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'product_name',
        'description',
        'price',
        'available_quantity',
        'reorder_level',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_quantity' => 'integer',
        'reorder_level' => 'integer',
    ];

    /**
     * Keep the product's inventory row in step with available_quantity.
     */
    protected static function booted(): void
    {
        static::created(function (Product $product): void {
            $product->syncInventory();
        });

        static::updated(function (Product $product): void {
            if ($product->wasChanged('available_quantity')) {
                $product->syncInventory();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_id', 'product_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'product_id', 'product_id');
    }

    /**
     * Products customers are allowed to see and buy.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Active products whose stock is at or below their reorder level.
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE)
            ->whereColumn('available_quantity', '<=', 'reorder_level')
            ->orderBy('available_quantity');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLowStock(): bool
    {
        return $this->available_quantity <= $this->reorder_level;
    }

    /**
     * Add to (positive) or take from (negative) the stock of this product.
     * Use this at checkout, on cancellation and on restock so two requests
     * can never sell the same last item.
     *
     * @throws RuntimeException when the change would make the stock negative
     */
    public function adjustStock(int $change): void
    {
        DB::transaction(function () use ($change): void {
            $lockedProduct = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $newQuantity = $lockedProduct->available_quantity + $change;

            if ($newQuantity < 0) {
                throw new RuntimeException("Not enough stock for {$lockedProduct->product_name}.");
            }

            $lockedProduct->update(['available_quantity' => $newQuantity]);
        });

        $this->refresh();
    }

    /**
     * Copy available_quantity into the product's single inventory row.
     */
    public function syncInventory(): void
    {
        $quantity = (int) ($this->available_quantity ?? 0);
        $inventory = $this->inventory()->first();

        if ($inventory === null) {
            $inventory = new Inventory(['product_id' => $this->getKey()]);
        } elseif ($inventory->quantity_in_stock === $quantity) {
            return;
        }

        $inventory->fill([
            'quantity_in_stock' => $quantity,
            'last_stock_update' => now(),
        ])->saveQuietly();
    }

    /**
     * Top products by units sold in completed orders.
     *
     * @return Collection<int, Product>
     */
    public static function bestSellers(int $limit = 5): Collection
    {
        return static::query()
            ->join('order_items', 'order_items.product_id', '=', 'products.product_id')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->where('orders.order_status', Order::STATUS_COMPLETED)
            ->groupBy('products.product_id', 'products.product_name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get([
                'products.product_id',
                'products.product_name',
                DB::raw('SUM(order_items.quantity) AS units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.item_price) AS revenue'),
            ]);
    }

    /**
     * Validation rules for the product form. The image rule validates the
     * uploaded file; store the file and save its path in the image column.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,category_id'],
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'available_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ];
    }
}
