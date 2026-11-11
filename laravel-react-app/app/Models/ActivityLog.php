<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    protected $table = 'logs';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Newest log entries first.
     */
    public function scopeRecent(Builder $query): void
    {
        $query->orderByDesc('timestamp')->orderByDesc('log_id');
    }

    /**
     * Write one log entry, e.g. ActivityLog::record($user, 'Logged in').
     */
    public static function record(User|int $user, string $action): self
    {
        return static::create([
            'user_id' => $user instanceof User ? $user->user_id : $user,
            'action' => $action,
            'timestamp' => now(),
        ]);
    }
}
