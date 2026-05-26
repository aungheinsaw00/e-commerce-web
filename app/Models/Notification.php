<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    const TYPE_ORDER_PLACED           = 'ORDER_PLACED';
    const TYPE_ORDER_STATUS_UPDATED   = 'ORDER_STATUS_UPDATED';
    const TYPE_REFUND_REQUESTED       = 'REFUND_REQUESTED';
    const TYPE_REFUND_APPROVED        = 'REFUND_APPROVED';
    const TYPE_REFUND_REJECTED        = 'REFUND_REJECTED';

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'read_at', 'data',
    ];

    protected $appends = ['route'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at'  => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Notification $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function getRouteAttribute(): ?string
    {
        $type = $this->type;
        $data = $this->data;

        if (!$data) {
            return null;
        }

        switch ($type) {
            case self::TYPE_ORDER_PLACED:
            case self::TYPE_ORDER_STATUS_UPDATED:
                return isset($data['order_id']) ? route('admin.orders.show', $data['order_id']) : null;
            case self::TYPE_REFUND_REQUESTED:
            case self::TYPE_REFUND_APPROVED:
            case self::TYPE_REFUND_REJECTED:
                return route('admin.refunds.index');
            default:
                if (isset($data['route'])) {
                    return $data['route'];
                }
                return null;
        }
    }
}
