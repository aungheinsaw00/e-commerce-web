<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'variant_id', 'type', 'quantity', 'reference_type', 'reference_id', 'note', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const TYPE_RESERVE = 'RESERVE';
    const TYPE_RELEASE = 'RELEASE';

    protected static function booted(): void
    {
        static::creating(function (InventoryMovement $movement) {
            $movement->created_at = $movement->created_at ?? now();
        });

        // Ensure consistent ordering by insertion order (id ASC) for audit trails
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('id', 'asc');
        });
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
