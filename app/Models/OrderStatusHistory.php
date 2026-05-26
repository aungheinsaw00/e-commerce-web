<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'from_status', 'to_status', 'note'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderStatusHistory $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
