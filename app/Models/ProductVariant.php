<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'name', 'price_override', 'is_active'];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'variant_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price_override ?? $this->product->base_price);
    }

    public function getAvailableStockAttribute(): int
    {
        $inv = $this->inventory;
        if (!$inv) return 0;
        return max(0, $inv->stock_quantity - $inv->reserved_quantity);
    }
}
