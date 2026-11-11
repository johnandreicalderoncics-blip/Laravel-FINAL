<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    const PAYMENT_METHODS = ['cash_on_delivery', 'gcash', 'bank_transfer', 'card'];

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id',
        'order_date',
        'total_price',
        'order_status',
        'shipping_address',
        'contact_number',
        'payment_method',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    /**
     * Default the order date to today when it isn't given.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->order_date ??= now()->toDateString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    /**
     * Newest orders first. Chain ->limit(10) or ->paginate() as needed.
     */
    public function scopeRecent(Builder $query): void
    {
        $query->orderByDesc('order_date')->orderByDesc('order_id');
    }

    /**
     * Orders that count as sales.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('order_status', self::STATUS_COMPLETED);
    }

    public function scopeStatus(Builder $query, string $status): void
    {
        $query->where('order_status', $status);
    }

    /**
     * Total sales: the sum of total_price over completed orders, optionally
     * limited to a date range (inclusive).
     */
    public static function totalSales(DateTimeInterface|string|null $from = null, DateTimeInterface|string|null $to = null): float
    {
        return (float) static::query()
            ->completed()
            ->when($from, fn (Builder $query) => $query->whereDate('order_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('order_date', '<=', $to))
            ->sum('total_price');
    }

    /**
     * Completed sales per day for the last N days, newest day first.
     *
     * @return Collection<int, object{order_date: string, orders_count: int, sales: string}>
     */
    public static function dailySales(int $days = 7): Collection
    {
        return static::query()
            ->completed()
            ->whereDate('order_date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('order_date')
            ->orderByDesc('order_date')
            ->toBase()
            ->get([
                'order_date',
                DB::raw('COUNT(*) AS orders_count'),
                DB::raw('SUM(total_price) AS sales'),
            ]);
    }

    /**
     * Number of orders in each status, keyed by status.
     *
     * @return Collection<string, int>
     */
    public static function countByStatus(): Collection
    {
        return static::query()
            ->toBase()
            ->groupBy('order_status')
            ->pluck(DB::raw('COUNT(*)'), 'order_status')
            ->map(fn (mixed $count): int => (int) $count);
    }

    /**
     * Recompute total_price from the order's items and save it.
     */
    public function recalculateTotal(): void
    {
        $total = $this->orderItems()->sum(DB::raw('quantity * item_price'));

        $this->update(['total_price' => $total]);
    }

    /**
     * Validation rules for the checkout form.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:1000'],
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'],
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
        ];
    }
}
